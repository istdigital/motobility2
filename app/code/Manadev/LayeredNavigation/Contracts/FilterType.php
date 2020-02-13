<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Contracts;

interface FilterType {
    /**
     * Returns registry of filter templates available for ths filter type
     * @return FilterTemplates
     */
    public function getTemplates();

    /**
     * Returns (possible modified) array of field definitions tailored for this filter type.
     *
     * @param array $fields
     * @return array
     */
    public function refineFields($fields);

    /**
     * Returns one of \Manadev\Seo\Enums\UrlKeyType values
     *
     * @return int
     */
    public function getSeoParameterSubType();
}