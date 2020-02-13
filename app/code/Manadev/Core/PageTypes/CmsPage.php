<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\PageTypes;

use Manadev\Core\Configuration;
use Manadev\Core\Contracts\PageType;
use Manadev\LayeredNavigation\Resources\Collections\FilterCollection;

class CmsPage extends PageType
{
    /**
     * @var Configuration
     */
    protected $configuration;

    public function __construct(Configuration $configuration) {
        $this->configuration = $configuration;
    }

    /**
     * @param FilterCollection $filters
     */
    public function limitFilterCollection($filters) {
        $filters->addFieldToFilter('is_enabled_in_categories', 1);
    }

    /**
     * @param \Manadev\Seo\Data\RouteData $route
     * @return array | null
     */
    public function getUrlKeySearchCondition($route) {
        if (!isset($route->params['id'])) {
            return null;
        }

        return [
            "`sub_type` = ?" => 'cms_page',
            "`cms_page_id` = ?" => $route->params['id'],
        ];
    }

    public function getConfigKey() {
        return 'cms_pages';
    }
}