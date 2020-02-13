<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Sources;

use Manadev\Core\Source;
use Manadev\LayeredNavigation\Contracts\FilterTemplates;

class TemplateSource extends Source
{
    /**
     * @var FilterTemplates
     */
    protected $filterTemplates;

    public function __construct(FilterTemplates $filterTemplates) {

        $this->filterTemplates = $filterTemplates;
    }

    public function getOptions() {
        $result = [];
        
        foreach ($this->filterTemplates->getList() as $key => $filterTemplate) {
            $result[$key] = $filterTemplate->getTitle();
        }

        asort($result);
        return $result;
    }
}