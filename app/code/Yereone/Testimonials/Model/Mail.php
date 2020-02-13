<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Yereone\Testimonials\Model;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Area;
use Magento\Framework\DataObject;


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
        \Yereone\Testimonials\Helper\Data $helper,
        StoreManagerInterface $storeManager = null
    ) {
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
    public function send($data)
    {
        $this->inlineTranslation->suspend();
        try {

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
                ->setReplyTo($data['email'],$data['author'])
                ->getTransport();
            $transport->sendMessage();

        } finally {
            $this->inlineTranslation->resume();
        }
    }
}
