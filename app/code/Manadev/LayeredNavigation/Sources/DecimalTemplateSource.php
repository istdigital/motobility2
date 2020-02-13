<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Sources;

use Manadev\Core\Source;
use Manadev\LayeredNavigation\Registries\FilterTemplates\DecimalFilterTemplates;

class DecimalTemplateSource extends Source
{
    /**
     * @var DecimalFilterTemplates
     */
    protected $filterTemplates;

    public function __construct(DecimalFilterTemplates $filterTemplates) {
        $this->filterTemplates = $filterTemplates;
    }

    public function getOptions() {
        $options = $this->filterTemplates->getSource()->getOptions();
        unset($options['min_max_slider']);

        return $options;
    }
}