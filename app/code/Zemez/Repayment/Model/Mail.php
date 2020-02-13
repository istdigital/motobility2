<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Zemez\Repayment\Model;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Area;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\Helper\Data;

class Mail
{

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
    
    /**
     * @var helper
     */
    private $helper;

    private $pricehelper;


    /**
     * Initialize dependencies.
     *
     * @param ConfigInterface $contactsConfig
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        \Zemez\Repayment\Helper\Data $helper,
        \Magento\Framework\Pricing\Helper\Data $pricehelper,
        StoreManagerInterface $storeManager = null
    ) {
        $this->helper = $helper;
        $this->pricehelper = $pricehelper;
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
    public function send($data)
    {
        $this->inlineTranslation->suspend();
        try {

            $data['price'] = $this->pricehelper->currency($data['price'], true, false);
            $data['application_fee'] = $this->pricehelper->currency($data['application_fee'], true, false);
            $data['deposit'] = $this->pricehelper->currency($data['deposit'], true, false);
            $data['sub_total'] = $this->pricehelper->currency($data['sub_total'], true, false);
            $data['payment_charge'] = $this->pricehelper->currency($data['payment_charge'], true, false);
            $data['total'] = $this->pricehelper->currency($data['total'], true, false);

            //SEND EMAIL TO USER
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($this->helper->getUserEmailTemplate())
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId()
                    ]
                )
                ->setTemplateVars(['data' => new DataObject($data)])
                ->addTo($data['email'])
                ->setReplyTo($this->helper->getUserEmailSender(), $this->storeManager->getStore()->getName())
                ->getTransport();
            $transport->sendMessage();


            //SEND EMAIL TO ADMIN
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($this->helper->getAdminEmailTemplate())
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId()
                    ]
                )
                ->setTemplateVars(['data' => new DataObject($data)])
                ->addTo($this->helper->getAdminEmailReceiver())
                ->setReplyTo($data['email'],$data['name'])
                ->getTransport();
            $transport->sendMessage();

        } finally {
            $this->inlineTranslation->resume();
        }
    }
}
