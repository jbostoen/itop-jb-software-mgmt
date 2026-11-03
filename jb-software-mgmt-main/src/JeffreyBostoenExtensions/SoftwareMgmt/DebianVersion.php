<?php

/**
 * @copyright   Copyright (c) 2026 Jeffrey Bostoen
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version     3.2.261103
 */

namespace JeffreyBostoenExtensions\SoftwareMgmt;

/**
 * Class DebianVersion. Compares the versions of Debian and Ubuntu packages: [epoch:]upstream[-revision], with the dpkg algorithm.
 * PHP's version_compare() does not know the epoch, nor the tilde, and orders "1:9.6p1-3ubuntu13.20" wrongly.
 */
abstract class DebianVersion {

	/**
	 * @param string $sA
	 * @param string $sB
	 *
	 * @return int Negative when A is older than B, 0 when equal, positive when newer.
	 */
	public static function Compare(string $sA, string $sB): int {

		[$iEpochA, $sUpstreamA, $sRevisionA] = static::Split($sA);
		[$iEpochB, $sUpstreamB, $sRevisionB] = static::Split($sB);

		if($iEpochA !== $iEpochB) {
			return $iEpochA <=> $iEpochB;
		}

		$iCompare = static::ComparePart($sUpstreamA, $sUpstreamB);

		return ($iCompare !== 0 ? $iCompare : static::ComparePart($sRevisionA, $sRevisionB));

	}

	/**
	 * @param string $sVersion
	 *
	 * @return array{0: int, 1: string, 2: string} Epoch, upstream version, revision.
	 */
	private static function Split(string $sVersion): array {

		$sVersion = trim($sVersion);
		$iEpoch = 0;

		if(preg_match('/^(\d+):(.*)$/', $sVersion, $aMatch) === 1) {
			$iEpoch = (int)$aMatch[1];
			$sVersion = $aMatch[2];
		}

		$iPos = strrpos($sVersion, '-');

		return (is_int($iPos) ? [$iEpoch, substr($sVersion, 0, $iPos), substr($sVersion, $iPos + 1)] : [$iEpoch, $sVersion, '']);

	}

	/**
	 * @param string $sA
	 * @param string $sB
	 *
	 * @return int The dpkg algorithm: letters before non-letters, "~" before everything, digits as numbers.
	 */
	private static function ComparePart(string $sA, string $sB): int {

		$iA = 0;
		$iB = 0;
		$iLengthA = strlen($sA);
		$iLengthB = strlen($sB);

		while($iA < $iLengthA || $iB < $iLengthB) {

			$iFirstDiff = 0;

			while(($iA < $iLengthA && !ctype_digit($sA[$iA])) || ($iB < $iLengthB && !ctype_digit($sB[$iB]))) {

				$iOrderA = static::GetOrder($sA[$iA] ?? '');
				$iOrderB = static::GetOrder($sB[$iB] ?? '');

				if($iOrderA !== $iOrderB) {
					return $iOrderA <=> $iOrderB;
				}

				$iA++;
				$iB++;

			}

			while($iA < $iLengthA && $sA[$iA] === '0') {
				$iA++;
			}

			while($iB < $iLengthB && $sB[$iB] === '0') {
				$iB++;
			}

			while($iA < $iLengthA && ctype_digit($sA[$iA]) && $iB < $iLengthB && ctype_digit($sB[$iB])) {

				if($iFirstDiff === 0) {
					$iFirstDiff = ord($sA[$iA]) <=> ord($sB[$iB]);
				}

				$iA++;
				$iB++;

			}

			if($iA < $iLengthA && ctype_digit($sA[$iA])) {
				return 1;
			}

			if($iB < $iLengthB && ctype_digit($sB[$iB])) {
				return -1;
			}

			if($iFirstDiff !== 0) {
				return $iFirstDiff;
			}

		}

		return 0;

	}

	/**
	 * @param string $sCharacter
	 *
	 * @return int Sort weight of one character in a dpkg version.
	 */
	private static function GetOrder(string $sCharacter): int {

		if($sCharacter === '' || ctype_digit($sCharacter)) {
			return 0;
		}

		if($sCharacter === '~') {
			return -1;
		}

		return (ctype_alpha($sCharacter) ? ord($sCharacter) : ord($sCharacter) + 256);

	}

}
