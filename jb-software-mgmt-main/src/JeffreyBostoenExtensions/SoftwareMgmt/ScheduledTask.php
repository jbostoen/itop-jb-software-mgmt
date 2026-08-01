<?php

/**
 * @copyright   Copyright (c) 2019-2026 Jeffrey Bostoen
 * @license     See license.md
 * @version     3.2.260801
 *
 */

namespace JeffreyBostoenExtensions\SoftwareMgmt;

// iTop.
use iScheduledProcess;
use MetaModel;

// Generic.
use DateTime;
use Exception;

/**
 * Class ScheduledTask. A task that performs routine maintenance operations.
 */
class ScheduledTask implements iScheduledProcess {
	
	/**
	 * @inheritDoc
	 */
	public function GetNextOccurrence() {

		$sDate = date('Y-m-d', strtotime('+1 day'));
		$sTime = MetaModel::GetModuleSetting(Helper::MODULE_CODE, 'time', '00:05');
		
		try {
			
			return new DateTime($sDate.' '.$sTime.':00');
		
		}
		catch(Exception $e) {
						
			return new DateTime($sDate.' 00:05:00');
			
		}
		
		
	}
	
	
	/**
	 * @inheritdoc
	 */
	public function Process($iTimeLimit) {
		
		Helper::Trace('Background task started.');
		
		try {
			
			// - Ensure integrity on the "status" of the SoftwareBuild.
				Helper::UpdateStatusOfSoftwareBuilds([], []);
			
		}
		catch(Exception $e) {
			Helper::Trace($e->GetMessage());
		}
		
		Helper::Trace('Background task finished.');
		
	}
	
	
}
