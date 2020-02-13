<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Zemez\StockAlert\Model;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Area;

class Mail
{
    private $helper;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    protected $_productloader;  

    protected $imageBuilder;  

    /**
     * Initialize dependencies.
     *
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        \Zemez\StockAlert\Helper\Data $helper,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        StoreManagerInterface $storeManager = null
    ) {
        $this->imageBuilder = $imageBuilder;
        $this->_productloader = $_productloader;
        $this->helper = $helper;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * Send email from contact form
     *
     * @param string $replyTo
     * @param array $variables
     * @return void
     */
    public function sendToUser(array $variables)
    {
        /** @see \Magento\Contact\Controller\Index\Post::validatedParams() */
        try {

            $_product = $this->_productloader->create()->load($variables['data']['product_id']);
            $variables['data']['product_url'] = $_product->getProductUrl();
            #$variables['data']['product_image'] = $this->imageBuilder->create($_product, 'cart_page_product_thumbnail')->getImageUrl();
            $variables['data']['product_image'] = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)."catalog/product".$_product->getSmallImage();

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($this->helper->getUserEmailTemplate())
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId()
                    ]
                )
                ->setTemplateVars($variables)
                ->setFrom($this->helper->emailSender())
                ->addTo($variables['data']['email'])
                ->setReplyTo($this->helper->emailSenderMail(), $this->helper->emailSenderName())
                ->getTransport();

            $transport->sendMessage();
        } finally {
            $this->inlineTranslation->resume();
        }
    }

    public function sendToAdmin(array $variables)
    {
        /** @see \Magento\Contact\Controller\Index\Post::validatedParams() */
        $replyToName = !empty($variables['data']['name']) ? $variables['data']['name'] : null;
        $replyTo = $variables['data']['email'];

        $_product = $this->_productloader->create()->load($variables['data']['product_id']);

        $variables['data']['product_url'] = $_product->getProductUrl();
        $variables['data']['product_image'] = $this->imageBuilder->create($_product, 'cart_page_product_thumbnail')->getImageUrl();

        $this->inlineTranslation->suspend();
        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($this->helper->getAdminEmailTemplate())
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId()
                    ]
                )
                ->setTemplateVars($variables)
                ->setFrom($this->helper->emailSender())
                ->addTo($this->helper->emailRecipient())
                ->setReplyTo($replyTo, $replyToName)
                ->getTransport();

            $transport->sendMessage();
        } finally {
            $this->inlineTranslation->resume();
        }
    }

}
