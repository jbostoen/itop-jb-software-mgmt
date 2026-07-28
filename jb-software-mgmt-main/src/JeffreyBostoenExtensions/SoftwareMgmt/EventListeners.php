<?php
/**
 * @copyright   Copyright (c) 2021-2024 Jeffrey Bostoen
 * @license     See license.md
 * @version     2.7.240530
 */

namespace JeffreyBostoenExtensions\SoftwareMgmt;
 
// iTop.
use Combodo\iTop\Service\Events\{EventData, EventService, iEventServiceSetup};
use DBObject;
use DBObjectSearch;
use DBObjectSet;
use MetaModel;
use SoftwareBuild;

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
        
        // - With re-entrance protection active; it would not correctly update its own status.
            
            MetaModel::StopReentranceProtection($oObj);
            Helper::UpdateStatusOfSoftwareBuilds([ $oProduct->GetKey() ], []);
            MetaModel::StartReentranceProtection($oObj);


    }
    
    

}
