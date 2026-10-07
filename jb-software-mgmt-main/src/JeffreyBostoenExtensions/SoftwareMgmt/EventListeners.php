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
use MetaModel;
use SoftwareBuild;
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
