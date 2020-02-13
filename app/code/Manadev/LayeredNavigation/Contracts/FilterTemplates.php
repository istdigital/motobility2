<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Contracts;

use Manadev\LayeredNavigation\Sources\TemplateSource;

interface FilterTemplates {
    /**
     * Returns filter template by its internal name. Returns false if no filter template with specified name is
     * defined.
     *
     * @param $type
     * @return bool|FilterTemplate
     */
    public function get($type);

    /**
     * @return FilterTemplate[]
     */
    public function getList();

    /**
     * @return TemplateSource
     */
    public function getSource();
}