<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation;

use Magento\Store\Model\StoreManagerInterface;
use Manadev\LayeredNavigation\Models\Filter;
use Manadev\Core\Helper as CoreHelper;

class Helper {
    protected $filters = [];
    /**
     * @var Factory
     */
    protected $factory;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var CoreHelper
     */
    protected $coreHelper;

    public function __construct(Factory $factory, StoreManagerInterface $storeManager, CoreHelper $coreHelper) {
        $this->factory = $factory;
        $this->storeManager = $storeManager;
        $this->coreHelper = $coreHelper;
    }

    /**
     * @param int|null $storeId
     * @param string|null $route
     * @return Resources\Collections\FilterCollection
     */
    public function getAllFilters($storeId = null, $route = null) {
        $storeId = $storeId ?: $this->storeManager->getStore()->getId();
        $route = $route ?: $this->coreHelper->getCurrentRoute();
        $key = $storeId . '-' . $route;

        if (!isset($this->filters[$key])) {
            $filters = $this->factory->createFilterCollection()->storeSpecific($storeId);

            if ($pageType = $this->coreHelper->getPageType($route)) {
                $pageType->limitFilterCollection($filters);
            }

            $filters->orderByPosition();
            $this->filters[$key] = $filters;
        }

        return $this->filters[$key];
    }
}