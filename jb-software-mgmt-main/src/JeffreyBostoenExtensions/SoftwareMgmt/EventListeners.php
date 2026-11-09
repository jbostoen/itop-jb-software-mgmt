<?php
/**
 * @copyright   Copyright (c) 2021-2026 Jeffrey Bostoen
 * @license     See license.md
 * @version     3.2.260804
 */

namespace JeffreyBostoenExtensions\SoftwareMgmt;
 
// iTop.
use Combodo\iTop\Service\Events\{EventData, EventService, iEventServiceSetup};
use DBObject;
use DBObjectSearch;
use DBObjectSet;
use Dict;
use MetaModel;
use SoftwareBuild;
use SoftwareVersion;
use SoftwareVersionLifecycleEvent;

/**
 * Class EventListenerPersonal. Several personal event listeners.
 */
class EventListenerPersonal implements iEventServiceSetup {

    /**
     * @inheritDoc
     */
    public function RegisterEventsAndListeners() {

        EventService::RegisterListener(
            EVENT_DB_AFTER_WRITE,
            [$this, 'AfterWriteSoftwareBuild'],
            SoftwareBuild::class
        );
        
        EventService::RegisterListener(
            EVENT_DB_AFTER_DELETE,
            [$this, 'AfterDeleteSoftwareBuild'],
            SoftwareBuild::class
        );

        EventService::RegisterListener(
            EVENT_DB_COMPUTE_VALUES,
            [$this, 'ComputeValuesSoftwareBuild'],
            SoftwareBuild::class
        );

        EventService::RegisterListener(
            EVENT_DB_CHECK_TO_WRITE,
            [$this, 'CheckToWriteSoftwareBuild'],
            SoftwareBuild::class
        );

        EventService::RegisterListener(
            EVENT_DB_CHECK_TO_WRITE,
            [$this, 'CheckToWriteSoftwareVersion'],
            SoftwareVersion::class
        );

        EventService::RegisterListener(
            EVENT_DB_AFTER_WRITE,
            [$this, 'AfterWriteLifecycleEvent'],
            SoftwareVersionLifecycleEvent::class
        );

        EventService::RegisterListener(
            EVENT_DB_AFTER_DELETE,
            [$this, 'AfterDeleteLifecycleEvent'],
            SoftwareVersionLifecycleEvent::class
        );

    }


    /**
     * Fills the platform product of a software build from its platform version, and the platform family from the platform product, when
     * only the lower one is set (code, imports and older clients only know the version or the product).
     *
     * @param EventData $oEventData
     *
     * @return void
     */
    public function ComputeValuesSoftwareBuild(EventData $oEventData) {

        /** @var SoftwareBuild $oBuild The object. */
        $oBuild = $oEventData->Get('object');

        $iVersionId = (int)$oBuild->Get('platform_version_id');

        if($iVersionId > 0 && (int)$oBuild->Get('platform_product_id') <= 0) {

            $oVersion = MetaModel::GetObject('SoftwareVersion', $iVersionId, false, true);

            if($oVersion !== null) {
                $oBuild->Set('platform_product_id', (int)$oVersion->Get('softwareproduct_id'));
            }

        }

        // - A build that is not about a platform ("na", "unknown") takes the family of its platform product.
        if((int)$oBuild->Get('platform_product_id') > 0 && !Helper::IsPlatformFamily((string)$oBuild->Get('platform_family'))) {

            $oProduct = MetaModel::GetObject('SoftwareProduct', (int)$oBuild->Get('platform_product_id'), false, true);

            if($oProduct !== null && (string)$oProduct->Get('platform_family') !== '') {
                $oBuild->Set('platform_family', (string)$oProduct->Get('platform_family'));
            }

        }

    }


    /**
     * Refuses a platform version that is not a version of the platform product of the software build.
     *
     * @param EventData $oEventData
     *
     * @return void
     */
    public function CheckToWriteSoftwareBuild(EventData $oEventData) {

        /** @var SoftwareBuild $oBuild The object. */
        $oBuild = $oEventData->Get('object');

        $iVersionId = (int)$oBuild->Get('platform_version_id');
        $iProductId = (int)$oBuild->Get('platform_product_id');

        if($iProductId <= 0) {
            return;
        }

        if(!$oBuild->IsNew() && count(array_intersect_key($oBuild->ListChanges(), ['platform_family' => true, 'platform_product_id' => true, 'platform_version_id' => true])) === 0) {
            return;
        }

        if($iVersionId > 0) {

            $oVersion = MetaModel::GetObject('SoftwareVersion', $iVersionId, false, true);

            if($oVersion !== null && (int)$oVersion->Get('softwareproduct_id') !== $iProductId) {
                $oBuild->AddCheckIssue(Dict::Format('JBSoftwareMgmt:SoftwareBuild:PlatformVersionOfAnotherProduct', $oVersion->GetName(), $oBuild->Get('platform_product_id_friendlyname')));
            }

        }

        // - The family of the build is a real family: its platform product belongs to it.
        if(Helper::IsPlatformFamily((string)$oBuild->Get('platform_family'))) {

            $oProduct = MetaModel::GetObject('SoftwareProduct', $iProductId, false, true);

            if($oProduct !== null && (string)$oProduct->Get('platform_family') !== '' && (string)$oProduct->Get('platform_family') !== (string)$oBuild->Get('platform_family')) {
                $oBuild->AddCheckIssue(Dict::Format('JBSoftwareMgmt:SoftwareBuild:PlatformProductOfAnotherFamily', $oProduct->GetName(), $oBuild->GetAsPlainText('platform_family')));
            }

        }

    }


    /**
     * Warns when another software version of the same software has the same codename. A warning, not an error: it does not stop the save.
     *
     * @param EventData $oEventData
     *
     * @return void
     */
    public function CheckToWriteSoftwareVersion(EventData $oEventData) {

        /** @var SoftwareVersion $oVersion The object. */
        $oVersion = $oEventData->Get('object');

        $sCodename = trim((string)$oVersion->Get('codename'));

        if($sCodename === '') {
            return;
        }

        // - Nothing to check when neither the codename nor the software changed.
        if(!$oVersion->IsNew() && count(array_intersect_key($oVersion->ListChanges(), ['codename' => true, 'softwareproduct_id' => true])) === 0) {
            return;
        }

        $oOther = MetaModel::GetObjectFromOQL('SELECT SoftwareVersion WHERE softwareproduct_id = :product_id AND codename = :codename AND id != :id', [
            'product_id' => $oVersion->Get('softwareproduct_id'),
            'codename' => $sCodename,
            'id' => (int)$oVersion->GetKey(),
        ]);

        if($oOther !== null) {
            $oVersion->AddCheckWarning(Dict::Format('JBSoftwareMgmt:SoftwareVersion:DuplicateCodename', $sCodename, $oOther->GetName()));
        }

    }


    /**
     * After a software build is successfully saved, ensure integrity.
     * 
     * @param EventData $oEventData
     *
     * @return void
     */
    public function AfterWriteSoftwareBuild(EventData $oEventData) {

        // - Use the generic method.
        //   Any creation or modification of release type, build number, linked software version would require an integrity check.
        //   Product is not directly modifiable, so it should be safe to filter here.

        Helper::Trace('Execute integrity check due to updated software build info.');

        /** @var SoftwareBuild $oObj The object. */
        $oObj = $oEventData->Get('object');

        $oProduct = MetaModel::GetObjectFromOQL('
            SELECT SoftwareProduct AS sp 
            JOIN SoftwareVersion AS sv ON sv.softwareproduct_id = sp.id
            WHERE sv.id = :version_id
        ', [
            'version_id' => $oObj->Get('softwareversion_id')
        ]);
        
        // - No product found: nothing to recompute.
        if($oProduct === null) {
            return;
        }

        // - With re-entrance protection active; it would not correctly update its own status.

            MetaModel::StopReentranceProtection($oObj);
            Helper::UpdateStatusOfSoftwareBuilds([ $oProduct->GetKey() ], []);
            MetaModel::StartReentranceProtection($oObj);


    }
    
    

    /**
     * After a software build is successfully saved, ensure integrity.
     * 
     * @param EventData $oEventData
     *
     * @return void
     */
    public function AfterDeleteSoftwareBuild(EventData $oEventData) {

        // - Use the generic method.

        Helper::Trace('Execute integrity check due to deleted software build.');

        /** @var SoftwareBuild $oObj The object. */
        $oObj = $oEventData->Get('object');

        $oProduct = MetaModel::GetObjectFromOQL('
            SELECT SoftwareProduct AS sp 
            JOIN SoftwareVersion AS sv ON sv.softwareproduct_id = sp.id
            WHERE sv.id = :version_id
        ', [
            'version_id' => $oObj->Get('softwareversion_id')
        ]);
        
        // - No product found (e.g. the version or product is being deleted as well): nothing to recompute.
        if($oProduct === null) {
            return;
        }

        // - Other builds may need to be updated (e.g. if latest was just deleted, another one should be promoted).
            Helper::UpdateStatusOfSoftwareBuilds([ $oProduct->GetKey() ], [ $oObj->Get('softwareversion_id') ]);


    }

    /**
     * After a lifecycle event is saved, keep the "is_maintained" flag and the end-of-life date of the software version(s) in line.
     *
     * @param EventData $oEventData
     *
     * @return void
     */
    public function AfterWriteLifecycleEvent(EventData $oEventData) {

        /** @var SoftwareVersionLifecycleEvent $oObj The object. */
        $oObj = $oEventData->Get('object');

        $aPrevious = $oObj->ListPreviousValuesForUpdatedAttributes();

        $iVersionId = (int)$oObj->Get('softwareversion_id');
        $iPreviousVersionId = (int)($aPrevious['softwareversion_id'] ?? $iVersionId);
        $sPreviousType = ($aPrevious['type'] ?? $oObj->Get('type'));
        $sPreviousDate = (array_key_exists('date', $aPrevious) ? $aPrevious['date'] : $oObj->Get('date'));

        $sOldDate = ($sPreviousType === Helper::EVENT_TYPE_END_OF_SECURITY_SUPPORT && !empty($sPreviousDate) ? $sPreviousDate : null);
        $sNewDate = ($oObj->Get('type') === Helper::EVENT_TYPE_END_OF_SECURITY_SUPPORT && !empty($oObj->Get('date')) ? $oObj->Get('date') : null);

        Helper::Trace('Update the lifecycle flags due to a saved lifecycle event.');

        // - The event moved to another version: the previous one loses it.
        if($iPreviousVersionId !== $iVersionId) {
            Helper::SyncEndOfLifeDate($iPreviousVersionId, $sOldDate, null);
            $sOldDate = null;
        }

        Helper::SyncEndOfLifeDate($iVersionId, $sOldDate, $sNewDate);
        Helper::UpdateIsMaintained(array_unique([$iVersionId, $iPreviousVersionId]));

    }

    /**
     * After a lifecycle event is deleted, keep the "is_maintained" flag and the end-of-life date of the software version in line.
     *
     * @param EventData $oEventData
     *
     * @return void
     */
    public function AfterDeleteLifecycleEvent(EventData $oEventData) {

        /** @var SoftwareVersionLifecycleEvent $oObj The object. */
        $oObj = $oEventData->Get('object');

        $iVersionId = (int)$oObj->Get('softwareversion_id');

        Helper::Trace('Update the lifecycle flags due to a deleted lifecycle event.');

        if($oObj->Get('type') === Helper::EVENT_TYPE_END_OF_SECURITY_SUPPORT && !empty($oObj->Get('date'))) {
            Helper::SyncEndOfLifeDate($iVersionId, $oObj->Get('date'), null);
        }

        Helper::UpdateIsMaintained([$iVersionId]);

    }

}
