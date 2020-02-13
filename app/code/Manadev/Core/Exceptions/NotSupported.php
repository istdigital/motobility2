<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Exceptions;

use Exception;

class NotSupported extends Exception {
    public function __construct($message = "Feature is not supported.", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}