<?php
/**
 * @copyright   Copyright (c) 2021-2026 Jeffrey Bostoen
 * @license     See license.md
 * @version     3.2.260804
 */

namespace JeffreyBostoenExtensions\SoftwareMgmt;

// iTop.
use FileLog;
use LogAPI;

/**
 * Class Logger. Custom logger for this extension.
 */
class Logger extends LogAPI {

	/** @const string CHANNEL_DEFAULT Log channel. */
	const CHANNEL_DEFAULT = 'SoftwareMgmtLog';

	/** @const string LEVEL_DEFAULT Minimum log level when none is configured. */
	const LEVEL_DEFAULT = self::LEVEL_INFO;

	/** @var FileLog|null $m_oFileLog File log. Mind: must stay untyped (or FileLog-typed), as LogAPI::Enable() assigns a FileLog object. */
	protected static $m_oFileLog = null;

	/**
	 * Enables logging to a file.
	 *
	 * @param string|null $sTargetFile Target file. Defaults to log/software_mgmt.log.
	 *
	 * @return void
	 */
	public static function Enable($sTargetFile = null) : void {

		if(empty($sTargetFile)) {
			$sTargetFile = APPROOT.'log/software_mgmt.log';
		}

		parent::Enable($sTargetFile);

	}

	/**
	 * @inheritDoc
	 *
	 * Mind: the file log is enabled lazily on first use, so no bootstrap code is required.
	 */
	protected static function WriteLog(string $sLevel, string $sMessage, ?string $sChannel = null, ?array $aContext = []) : void {

		if(static::$m_oFileLog === null) {
			static::Enable();
		}

		parent::WriteLog($sLevel, $sMessage, $sChannel, $aContext);

	}

}
