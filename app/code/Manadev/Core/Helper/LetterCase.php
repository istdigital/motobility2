<?php
/**
 * Created by PhpStorm.
 * User: Vernard
 * Date: 8/10/2015
 * Time: 11:44 AM
 */

namespace Manadev\Core\Helper;


class LetterCase {

    public static $upperCaseCharacters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	public static $lowerCaseCharacters = 'abcdefghijklmnopqrstuvwxyz';
	public static $whitespaceCharacters = " \t\r\n";

	protected function _explodeIdentifier($identifier) {
		$result = array();
		$segment = substr($identifier, 0, 1);
		$mode = 0; // not recognized
		if ($segment == '_') { $mode = 1; $result[] = ''; $segment = ''; }
		elseif ($segment == '-') { $mode = 3; $result[] = ''; $segment = ''; }
		$allUppers = !$segment || strpos(self::$upperCaseCharacters, $segment) !== false;
		for ($i = 1; $i < strlen($identifier); $i++) {
			$ch = substr($identifier, $i, 1);
			switch ($mode) {
				case 0: // not recognized
					if ($ch == '_') {
						$mode = 1; // underscored
						$result[] = $segment;
						$segment = '';
					}
					elseif ($ch == '-') {
						$mode = 3; // hyphened
						$result[] = $segment;
						$segment = '';
					}
					elseif (strpos(self::$upperCaseCharacters, $ch) !== false) {
						if (!$allUppers) {
							$mode = 2; // case separated
							$result[] = $segment;
							$segment = '';
						}
						$segment .= $ch;
					}
					else {
						if (strpos(self::$lowerCaseCharacters, $ch) !== false) $allUppers = false;
						$segment .= $ch;
					}
					break;
				case 1: // underscored
					if ($ch == '_') {
						$result[] = $segment;
						$segment = '';
					}
					else {
						$segment .= $ch;
					}
					break;
				case 2: // case separated
					if (strpos(self::$upperCaseCharacters, $ch) !== false) {
						$result[] = $segment;
						$segment = '';
					}
					$segment .= $ch;
					break;
				case 3: // hyphened
					if ($ch == '-') {
						$result[] = $segment;
						$segment = '';
					}
					else {
						$segment .= $ch;
					}
					break;
				default:
					throw new \Exception('Not implemented.');
			}
		}
		if ($segment) $result[] = $segment;
		return $result;
	}

	public function pascalCased($identifier) {
		$result = '';
		foreach (self::_explodeIdentifier($identifier) as $segment) {
			$result .= ucfirst(strtolower($segment));
		}
		return $result;
	}

	public function camelCased($identifier) {
		$result = '';
		$first = true;
		foreach (self::_explodeIdentifier($identifier) as $segment) {
			if ($first) {
				$result .= strtolower($segment);
				$first = false;
			}
			else {
				$result .= ucfirst(strtolower($segment));
			}
		}
		return $result;
	}

	public function lowerCased($identifier) {
		$result = '';
		$separatorNeeded = false;
		foreach (self::_explodeIdentifier($identifier) as $segment) {
			if ($separatorNeeded) $result .= '_'; else $separatorNeeded = true;
			$result .= strtolower($segment);
		}
		return $result;
	}

	public function upperCased($identifier) {
		$result = '';
		$separatorNeeded = false;
		foreach (self::_explodeIdentifier($identifier) as $segment) {
			if ($separatorNeeded) $result .= '_'; else $separatorNeeded = true;
			$result .= strtoupper($segment);
		}
		return $result;
	}

	public function hyphenCased($identifier) {
		$result = '';
		$separatorNeeded = false;
		foreach (self::_explodeIdentifier($identifier) as $segment) {
			if ($separatorNeeded) $result .= '-'; else $separatorNeeded = true;
			$result .= strtolower($segment);
		}
		return $result;
	}

	public function endsWith($haystack, $needle) {
		return (strrpos($haystack, $needle) === strlen($haystack) - strlen($needle));
	}
	public function startsWith($haystack, $needle) {
		return (strpos($haystack, $needle) === 0);
	}
}