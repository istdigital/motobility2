<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FeaturedProduct\Controller\Adminhtml\Product\Widget;

class Chooser extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * Chooser Source action
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $uniqId = $this->getRequest()->getParam('uniq_id');
        $massAction = $this->getRequest()->getParam('use_massaction', false);
        $productTypeId = $this->getRequest()->getParam('product_type_id', null);
        $elementValue = $this->getRequest()->getParam('element_value');

        $filter = $this->getRequest()->getParam('filter');
        if($filter && is_null($elementValue)) {
            $valuesArr = $this->_helper->prepareFilterString($this->getRequest()->getParam('filter'));
            if(array_key_exists('products_ids',$valuesArr)) {
                $productsIdsJSON = $valuesArr['products_ids'];
                $productsIds = json_decode($productsIdsJSON,true);
                if(is_array($productsIds)) {
                    $elementValue = implode(',',$productsIds);
                    $this->getRequest()->setParam('products_ids_widgets',$productsIds);
                }
            }
        }

        $layout = $this->layoutFactory->create();
        $productsGrid = $layout->createBlock(
            'TemplateMonster\FeaturedProduct\Block\Adminhtml\Widget\Chooser',
            '',
            [
                'data' => [
                    'id' => $uniqId,
                    'use_massaction' => $massAction,
                    'product_type_id' => $productTypeId,
                    'category_id' => $this->getRequest()->getParam('category_id'),
                    'element_value' => $elementValue,
                    'can_display_container' => true
                ]
            ]
        );

        $html = $productsGrid->toHtml();

        if (!$this->getRequest()->getParam('products_grid')) {
            $categoriesTree = $layout->createBlock(
                'Magento\Catalog\Block\Adminhtml\Category\Widget\Chooser',
                '',
                [
                    'data' => [
                        'id' => $uniqId . 'Tree',
                        'node_click_listener' => $productsGrid->getCategoryClickListenerJs(),
                        'with_empty_node' => true,
                    ]
                ]
            );

            $sjon = '';
            if($elementValue) {
                $arr = explode(",", $elementValue);
                $sjon = json_encode($arr);
            }

            $html = $layout->createBlock('TemplateMonster\FeaturedProduct\Block\Adminhtml\Widget\Chooser\Container')
                ->setTreeHtml($categoriesTree->toHtml())
                ->setGridHtml($html)
                ->setElementValue($sjon)
                ->setStringValue($elementValue)
                ->setIdForJs($productsGrid->getId())
                ->toHtml();
        }

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents($html);
    }
}