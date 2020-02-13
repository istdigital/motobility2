<?php
/**
 * Created by PhpStorm.
 * User: Vernard
 * Date: 8/12/2015
 * Time: 1:02 AM
 */

namespace Manadev\Core\Controller\Adminhtml\ProductChooser;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\LayoutFactory;

class Index extends Action
{
    /**
     * @var Builder
     */
    protected $productBuilder;
    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param Action\Context $context
     * @param LayoutFactory $layoutFactory
     * @param Builder $productBuilder
     * @param Helper $helper
     */
    public function __construct(
        Action\Context $context,
        LayoutFactory $layoutFactory
    ) {
        $this->layoutFactory = $layoutFactory;
        parent::__construct($context);
    }

    public function execute($beforeHtmlCallback = null) {
        $resultLayout = $this->layoutFactory->create();
        $layout = $resultLayout->getLayout();
        $request = $this->getRequest();

        $uniqId = $request->getParam('uniq_id', 'm_product_chooser');

        $productsGrid = $layout->createBlock('Manadev\Core\Blocks\Adminhtml\Chooser\Product', '',
        [
            'data' => [
                'id' => $uniqId,
                'use_massaction' => $request->getParam('mass_action', true),
                'product_type_id' => $request->getParam('product_type_id', null),
                'category_id' => $request->getParam('category_id'),
            ]
        ]);
        
        if (!$request->getParam('products_grid')) {
            $categoriesTree = $layout->createBlock('Magento\Catalog\Block\Adminhtml\Category\Widget\Chooser', '', [
                'data' => [
                    'id' => $uniqId . 'Tree',
                    'node_click_listener' => $productsGrid->getCategoryClickListenerJs(),
                    'with_empty_node' => true,
                ]
            ]
            );

            $confirmButton = $layout->createBlock('Magento\Backend\Block\Widget\Button')->setData(array(
                'label' => __('Confirm'),
                'class' => 'action-primary m-confirm',
            ));

            if (is_array($beforeHtmlCallback)) {
                call_user_func($beforeHtmlCallback, $productsGrid, $categoriesTree);
            }
            $html = $layout->createBlock('Magento\Backend\Block\Template')
                    ->setTemplate('Manadev_Core::chooser.phtml')
                    ->setTreeHtml($categoriesTree->toHtml())
                    ->setGridHtml($productsGrid->toHtml())
                    ->setConfirmButtonHtml($confirmButton->toHtml())
                    ->toHtml();
        }
        else {
            if (is_array($beforeHtmlCallback)) {
                call_user_func($beforeHtmlCallback, $productsGrid);
            }
            $html = $productsGrid->toHtml();
        }

        $this->getResponse()->setBody($html);
    }
}