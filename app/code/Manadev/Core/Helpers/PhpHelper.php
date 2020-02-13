<?php

namespace Manadev\Core\Helpers;

class PhpHelper
{
    public function isCalledFrom($classOrMethod) {
        $classOrMethod = explode("::", $classOrMethod);
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $entry) {
            if (!isset($entry['class']) || $entry['class'] != $classOrMethod[0]) {
                continue;
            }

            if (isset($classOrMethod[1]) && (!isset($entry['function']) || $entry['function'] != $classOrMethod[1])) {
                continue;
            }

            return true;
        }

        return false;
    }
}