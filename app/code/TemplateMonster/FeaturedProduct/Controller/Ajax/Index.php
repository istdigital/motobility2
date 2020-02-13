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
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace TemplateMonster\FeaturedProduct\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\Blog\Helper\Data;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Index
 * @package Mageplaza\Blog\Controller\Post
 */
class Index extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * @var Json
     */
    private $serializer;


    private $widgetFactory;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Widget\Model\Widget\Instance $widgetFactory,
        Json $serializer = null
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->widgetFactory = $widgetFactory;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(Json::class);
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $type = $this->getRequest()->getParam('type');
        $page = $this->resultPageFactory->create();
        #$page->getLayout()->getUpdate()->removeHandle('default');

        switch ($type) {
            case 'homeNew':
                $widget = $this->widgetFactory->load('home_new_products','title');
                break;
            case 'homeSale':
                $widget = $this->widgetFactory->load('home_sale_products','title');
                break;
            default:
                $widget = $this->widgetFactory->load('home_featured_products','title');
                break;
            
        }
        if($widget){
            $p = $widget->getWidgetParameters();
            echo $page->getLayout()
              ->createBlock('TemplateMonster\FeaturedProduct\Block\FeaturedProduct\Widget\Product')
              ->setTemplate('TemplateMonster_FeaturedProduct::home-list.phtml')
              ->setData($p)
              ->setProductTypes(implode(",", $p['product_types']))
              ->setConditionsEncoded(json_encode($p['conditions']))
              ->toHtml();
        }
        exit();
    }
}