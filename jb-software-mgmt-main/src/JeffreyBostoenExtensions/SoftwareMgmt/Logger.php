<?php
/**
 * @copyright   Copyright (c) 2021-2024 Jeffrey Bostoen
 * @license     See license.md
 * @version     2.7.240530
 */

namespace JeffreyBostoenExtensions\SoftwareMgmt;

// iTop.
use LogAPI;

/**
 * Class Logger. Custom logger for this extension.
 */
class Logger extends LogAPI {

    const CHANNEL_DEFAULT = 'SoftwareMgmtLog';
    const LEVEL_DEFAULT = self::LEVEL_INFO;

    protected static ?string $m_oFileLog = null;

	public static function Enable($sTargetFile = null)
	{
		if (empty($sTargetFile)) {
			$sTargetFile = APPROOT.'log/software_mgmt.log';
		}
		parent::Enable($sTargetFile);
	}

}
