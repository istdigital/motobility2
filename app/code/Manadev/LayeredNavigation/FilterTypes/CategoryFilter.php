<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\FilterTypes;

use Manadev\LayeredNavigation\Contracts\FilterType;
use Manadev\LayeredNavigation\Registries\FilterTemplates\CategoryFilterTemplates;
use Manadev\LayeredNavigation\Contracts\FilterTemplates;

class CategoryFilter implements FilterType {
    /**
     * @var CategoryFilterTemplates
     */
    protected $templates;

    public function __construct(CategoryFilterTemplates $templates) {
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
        unset($fields['param_name']['global_use_default_label']);
        unset($fields['title']['global_use_default_label']);
        unset($fields['template']['global_use_default_label']);
        unset($fields['minimum_product_count_per_option']['global_use_default_label']);
        unset($fields['is_enabled_in_categories']['global_use_default_label']);
        unset($fields['is_enabled_in_search']['global_use_default_label']);

        return $fields;
    }

    /**
     * Returns one of \Manadev\Seo\Enums\UrlKeyType values
     *
     * @return int
     */
    public function getSeoParameterSubType() {
        return 'category_filter_parameter';
    }
}