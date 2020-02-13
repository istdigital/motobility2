<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Helper;

class Mb
{
	public function endsWith($haystack, $needle) {
		return (mb_strrpos($haystack, $needle) === mb_strlen($haystack) - mb_strlen($needle));
	}
	public function startsWith($haystack, $needle) {
		return (mb_strpos($haystack, $needle) === 0);
	}

}