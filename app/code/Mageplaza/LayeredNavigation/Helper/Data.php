<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_LayeredNavigation
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigation\Helper;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Mageplaza\LayeredNavigation\Model\Layer\Filter;

/**
 * Class Data
 * @package Mageplaza\LayeredNavigation\Helper
 */
class Data extends \Mageplaza\AjaxLayer\Helper\Data
{
    const FILTER_TYPE_SLIDER = 'slider';
    const FILTER_TYPE_LIST   = 'list';

    /** @var Filter */
    protected $filterModel;

    /**
     * @param $filters
     *
     * @return mixed
     */
    public function getLayerConfiguration($filters)
    {
        $filterParams = $this->_getRequest()->getParams();
        foreach ($filterParams as $key => $param) {
            $filterParams[$key] = htmlspecialchars($param);
        }

        $config = new DataObject([
            'active'             => array_keys($filterParams),
            'params'             => $filterParams,
            'isCustomerLoggedIn' => $this->objectManager->create(Session::class)->isLoggedIn(),
            'isAjax'             => $this->ajaxEnabled()
        ]);

        $this->getFilterModel()->getLayerConfiguration($filters, $config);

        return self::jsonEncode($config->getData());
    }

    /**
     * @return Filter
     */
    public function getFilterModel()
    {
        if (!$this->filterModel) {
            $this->filterModel = $this->objectManager->create(Filter::class);
        }

        return $this->filterModel;
    }

    /**
     * @return ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @param FilterInterface $filter
     * @param null $storeId
     *
     * @return string
     */
    public function getTooltipContent($filter, $storeId = null)
    {
        try {
            $store  = $this->storeManager->getStore($storeId);
            $labels = $filter->getAttributeModel()->getData('tooltip_content');
            if (isset($labels[$store->getId()])) {
                if (empty($labels[$store->getId()])) {
                    return $labels[Store::DEFAULT_STORE_ID];
                }

                return $labels[$store->getId()];
            }

            return '';
        } catch (LocalizedException $e) {
            $this->_logger->error($e);

            return '';
        }
    }
}
