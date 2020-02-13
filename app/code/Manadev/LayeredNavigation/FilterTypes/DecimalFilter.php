<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\FilterTypes;

use Manadev\LayeredNavigation\Contracts\FilterTemplates;
use Manadev\LayeredNavigation\Contracts\FilterType;
use Manadev\LayeredNavigation\Registries\FilterTemplates\DecimalFilterTemplates;

class DecimalFilter implements FilterType {
    /**
     * @var DecimalFilterTemplates
     */
    protected $templates;

    public function __construct(DecimalFilterTemplates $templates) {
        $this->templates = $templates;
    }

    /**
     * Returns registry of filter templates available for ths filter type
     * @return FilterTemplates
     */
    public function getTemplates() {
        return $this->templates;
    }

    /**
     * Returns (possible modified) array of field definitions tailored for this filter type.
     *
     * @param array $fields
     * @return array
     */
    public function refineFields($fields) {
        return $fields;
    }

    /**
     * Returns one of \Manadev\Seo\Enums\UrlKeyType values
     *
     * @return int
     */
    public function getSeoParameterSubType() {
        return 'range_filter_parameter';
    }
}