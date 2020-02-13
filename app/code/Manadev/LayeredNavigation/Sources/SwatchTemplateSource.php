<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Sources;

use Manadev\Core\Source;
use Manadev\LayeredNavigation\Registries\FilterTemplates\SwatchFilterTemplates;

class SwatchTemplateSource extends Source
{
    /**
     * @var SwatchFilterTemplates
     */
    protected $filterTemplates;

    public function __construct(SwatchFilterTemplates $filterTemplates) {
        $this->filterTemplates = $filterTemplates;
    }

    public function getOptions() {
        return $this->filterTemplates->getSource()->getOptions();
    }
}