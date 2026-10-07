<?php
/**
 * @copyright   Copyright (c) 2021-2026 Jeffrey Bostoen
 * @license     See license.md
 * @version     3.2.260804
 */

namespace JeffreyBostoenExtensions\SoftwareMgmt;
 
// iTop.
use DBObject;
use DBObjectSearch;
use DBObjectSet;
use MetaModel;

/**
 * Class Helper. Helper methods.
 */
abstract class Helper {

    /** @var string MODULE_CODE The module code. */
    const MODULE_CODE = 'jb-software-mgmt';

    /** @var string EVENT_TYPE_END_OF_SECURITY_SUPPORT The lifecycle event type that ends the support for security updates (end of life). */
    const EVENT_TYPE_END_OF_SECURITY_SUPPORT = 'end_of_security_support';


	/**
	 * Trace function used for debugging.
	 *
	 * @param string $sMessage The message.
	 * @param mixed ...$args
	 *
	 * @return void
	 */
	public static function Trace($sMessage, ...$args) : void {
		
        $sMessage = call_user_func_array('sprintf', func_get_args());

        Logger::Trace($sMessage);
		
	}
    
    
    /**
     * This method will check all software builds.
     * 
     * It will mark:
     * - Builds as unsupported if the software version's end of life date passed.
     * - Builds as latest if they are the latest build for their software version and release type.
     * 
     * @param int[] $iTargetProductIds Optional. The product IDs to check. If empty, all products will be checked.
     * @param int[] $iTargetVersionIds Optional. The version IDs to check. If empty, all versions will be checked.
     *
     * @return void
     */
    public static function UpdateStatusOfSoftwareBuilds(array $iTargetProductIds, array $iTargetVersionIds) : void {


        // - Below, in an attempt to reduce memory usage, 
        //   a layered approach is used.

            $oFilterProducts = DBObjectSearch::FromOQL_AllData('SELECT SoftwareProduct');
            if(count($iTargetProductIds) > 0) {
                $oFilterProducts->AddCondition('id', $iTargetProductIds, 'IN');
            }

            $oSetProducts = new DBObjectSet($oFilterProducts);


            while($oProduct = $oSetProducts->Fetch()) {

                Helper::Trace('Product: %1$s', $oProduct->Get('friendlyname'));

                $oFilterVersions = DBObjectSearch::FromOQL_AllData('
                    SELECT SoftwareVersion 
                    WHERE 
                        softwareproduct_id = :softwareproduct_id'
                );
                if(count($iTargetVersionIds) > 0) {
                    $oFilterProducts->AddCondition('id', $iTargetVersionIds, 'IN');
                }

                $oSetVersions = new DBObjectSet($oFilterVersions, [], [
                    'softwareproduct_id' => $oProduct->GetKey(),
                ]);

                while($oVersion = $oSetVersions->Fetch()) {

                    Helper::Trace('Version: %1$s', $oProduct->Get('friendlyname'));
                    

                    $oSetBuilds = new DBObjectSet(DBObjectSearch::FromOQL_AllData('
                        SELECT SoftwareBuild 
                        WHERE 
                            softwareversion_id = :softwareversion_id
                    '), [], [
                        'softwareversion_id' => $oVersion->GetKey(),
                    ]);

                    // - Check if EOL.
                        
                        if($oVersion->Get('end_of_life_date') !== null && $oVersion->Get('end_of_life_date') !== '' && strtotime('now') > strtotime($oVersion->Get('end_of_life_date'))) {

                            Helper::Trace('Version is EOL.');

                            while($oBuild = $oSetBuilds->Fetch()) {

                                $oBuild->Set('status', 'unsupported');
                                $oBuild->DBUpdate();

                            }

                            continue;

                        }

                    // - Check if latest.
                    //   To do so, create a map of the release type and latest build number found.
                    //   Then, go over the builds again and mark them as latest or outdated.

                        $aReleaseTypeToLatestBuildNumber = [];

                        while($oBuild = $oSetBuilds->Fetch()) {

                            $sReleaseTypeId = $oBuild->Get('softwarereleasetype_id');
                            $sBuildNumber = $oBuild->Get('build_number');

                            if(!isset($aReleaseTypeToLatestBuildNumber[$sReleaseTypeId]) || version_compare($sBuildNumber, $aReleaseTypeToLatestBuildNumber[$sReleaseTypeId], '>')) {
                                $aReleaseTypeToLatestBuildNumber[$sReleaseTypeId] = $sBuildNumber;
                            }

                        }

                        $oSetBuilds->Rewind();

                        while($oBuild = $oSetBuilds->Fetch()) {


                            $sReleaseTypeId = $oBuild->Get('softwarereleasetype_id');
                            $sBuildNumber = $oBuild->Get('build_number');

                            if($sBuildNumber === $aReleaseTypeToLatestBuildNumber[$sReleaseTypeId]) {
                                $oBuild->Set('status', 'latest');
                            } 
                            else {
                                $oBuild->Set('status', 'outdated');
                            }

                            $oBuild->DBUpdate();

                        }
                    


                }

            }



    }



    
    

    /**
     * Recomputes "is_maintained" of software versions, based on their lifecycle events.
     *
     * - no: an "end of security support" event exists that is dated today or in the past, or has no date.
     * - yes: events are known, but no such event.
     * - unknown: no events at all.
     *
     * Only versions of which the value changes are updated.
     *
     * @param int[] $aVersionIds Optional. The version IDs to check. If empty, all versions will be checked.
     *
     * @return void
     */
    public static function UpdateIsMaintained(array $aVersionIds) : void {

        $sToday = date('Y-m-d');

        $oSearchEvents = DBObjectSearch::FromOQL_AllData('SELECT SoftwareVersionLifecycleEvent');

        if(count($aVersionIds) > 0) {
            $oSearchEvents->AddCondition('softwareversion_id', $aVersionIds, 'IN');
        }

        $oSetEvents = new DBObjectSet($oSearchEvents);
        $oSetEvents->OptimizeColumnLoad(['SoftwareVersionLifecycleEvent' => ['softwareversion_id', 'type', 'date']]);

        /** @var bool[] $aWithEvents Version ID => true. */
        $aWithEvents = [];

        /** @var bool[] $aEnded Version ID => true. */
        $aEnded = [];

        while($oEvent = $oSetEvents->Fetch()) {

            $iVersionId = (int)$oEvent->Get('softwareversion_id');
            $aWithEvents[$iVersionId] = true;

            if($oEvent->Get('type') === static::EVENT_TYPE_END_OF_SECURITY_SUPPORT) {

                $sDate = (string)$oEvent->Get('date');

                if($sDate === '' || $sDate <= $sToday) {
                    $aEnded[$iVersionId] = true;
                }

            }

        }

        static::SetIsMaintained(array_keys($aEnded), 'no');
        static::SetIsMaintained(array_keys(array_diff_key($aWithEvents, $aEnded)), 'yes');

        // - Versions without events (anymore): unknown.
        //   Only the versions that currently have another value are fetched.

            $oSearchOthers = DBObjectSearch::FromOQL_AllData("SELECT SoftwareVersion WHERE is_maintained != 'unknown'");

            if(count($aVersionIds) > 0) {
                $oSearchOthers->AddCondition('id', $aVersionIds, 'IN');
            }

            $oSetOthers = new DBObjectSet($oSearchOthers);

            while($oVersion = $oSetOthers->Fetch()) {

                if(!isset($aWithEvents[(int)$oVersion->GetKey()])) {
                    $oVersion->Set('is_maintained', 'unknown');
                    $oVersion->DBUpdate();
                }

            }

    }

    /**
     * Sets "is_maintained" on the given versions, if not set yet.
     *
     * @param int[] $aVersionIds
     * @param string $sValue
     *
     * @return void
     */
    protected static function SetIsMaintained(array $aVersionIds, string $sValue) : void {

        foreach(array_chunk($aVersionIds, 500) as $aChunk) {

            $oSet = new DBObjectSet(DBObjectSearch::FromOQL_AllData('SELECT SoftwareVersion WHERE id IN (:ids) AND is_maintained != :value'), [], [
                'ids' => $aChunk,
                'value' => $sValue,
            ]);

            while($oVersion = $oSet->Fetch()) {
                $oVersion->Set('is_maintained', $sValue);
                $oVersion->DBUpdate();
            }

        }

    }

    /**
     * Lets the end-of-life date of a software version follow its "end of security support" event.
     *
     * The date is only written when it is empty, or still holds the date that was written from the event before.
     * A date that was entered differently, or that is mastered by a synchronization, is left alone.
     *
     * @param int $iVersionId The software version.
     * @param string|null $sOldDate The date of the event before the change (null if there was none).
     * @param string|null $sNewDate The date of the event after the change (null if there is none).
     *
     * @return void
     */
    public static function SyncEndOfLifeDate(int $iVersionId, ?string $sOldDate, ?string $sNewDate) : void {

        $oVersion = MetaModel::GetObject('SoftwareVersion', $iVersionId, false, true);

        if($oVersion === null) {
            return;
        }

        $aReasons = [];

        if(($oVersion->GetSynchroReplicaFlags('end_of_life_date', $aReasons) & OPT_ATT_SLAVE) !== 0) {
            return;
        }

        $sCurrentDate = (string)$oVersion->Get('end_of_life_date');

        if($sCurrentDate === (string)$sNewDate || ($sCurrentDate !== '' && $sCurrentDate !== (string)$sOldDate)) {
            return;
        }

        $oVersion->Set('end_of_life_date', $sNewDate);
        $oVersion->DBUpdate();

    }

}
