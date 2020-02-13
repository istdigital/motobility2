<?php

/**
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace TemplateMonster\AjaxCatalog\Plugin\ProductList;

use TemplateMonster\AjaxCatalog\Helper\Catalog\View\ContentAjaxResponse;

class Toolbar
{
    /**
     * @var ContentAjaxResponse
     */
    protected $_helper;

    public function __construct(ContentAjaxResponse $helper)
    {
        $this->_helper = $helper;
    }

    /**
     * Add custom options for ToolBar widget.
     *
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterGetWidgetOptionsJson(\Magento\Catalog\Block\Product\ProductList\Toolbar $subject, $result)
    {
        return $this->_helper->addActiveAjaxFilter($result);
    }

    public function aroundSetCollection(
        \Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
        \Closure $proceed,
        $collection
    ) {
        $currentOrder = $subject->getCurrentOrder();
        $result = $proceed($collection);

        if ($currentOrder) {
            if ($currentOrder == 'high_to_low') {
                $subject->getCollection()->setOrder('price', 'desc');
            } elseif ($currentOrder == 'low_to_high') {
                $subject->getCollection()->setOrder('price', 'asc');
            }elseif ($currentOrder == 'a_to_z') {
                $subject->getCollection()->setOrder('name', 'asc');
            }elseif ($currentOrder == 'z_to_a') {
                $subject->getCollection()->setOrder('name', 'desc');
            }
        }

        return $result;
    }
}
