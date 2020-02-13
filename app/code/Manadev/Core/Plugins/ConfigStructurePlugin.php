<?php

namespace Manadev\Core\Plugins;

class ConfigStructurePlugin
{
    public function aroundGetElementByConfigPath($subject, callable $proceed, $path) {
        if ($path == 'manadev/updated_at' || $path == 'manadev/features') {
            return null;
        }

        return $proceed($path);
    }
}