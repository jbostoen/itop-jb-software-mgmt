<?php
/**
 * @copyright   Copyright (c) 2021-2026 Jeffrey Bostoen
 * @license     See license.md
 * @version     3.2.260729
 */

namespace JeffreyBostoenExtensions\SoftwareMgmt;
 
// iTop.
use DBObject;
use DBObjectSearch;
use DBObjectSet;

/**
 * Class Helper. Helper methods.
 */
abstract class Helper {

    /** @var string MODULE_CODE The module code. */
    const MODULE_CODE = 'jb-software-mgmt';


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



    
    

}
