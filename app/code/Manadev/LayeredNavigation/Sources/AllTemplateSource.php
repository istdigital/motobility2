<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Sources;

use Manadev\Core\Source;
use Manadev\LayeredNavigation\Registries\FilterTypes;

class AllTemplateSource extends Source
{
    /**
     * @var FilterTypes
     */
    protected $filterTypes;

    public function __construct(FilterTypes $filterTypes) {
        $this->filterTypes = $filterTypes;
    }

    public function getOptions() {
        $result = [];

        foreach ($this->filterTypes->getList() as $filterType) {
            foreach ($filterType->getTemplates()->getList() as $key => $filterTemplate) {
                $result[$key] = $filterTemplate->getTitle();
            }
        }

        asort($result);
        return $result;
    }
}