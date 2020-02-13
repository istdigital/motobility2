<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Zemez\Repayment\Controller\Index;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Zemez\Repayment\Model\Mail;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\App\Response\RedirectInterface;

class Post extends \Magento\Framework\App\Action\Action
{   
    /**
     * @var Context
     */
    private $context;

    /**
     * @var MailInterface
     */
    private $mail;

    /**
     * @var LoggerInterface
     */
    private $logger;



    private $enquiryFactory;


    private $redirect;

    /**
     * @param Context $context
     * @param ConfigInterface $contactsConfig
     * @param MailInterface $mail
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        \Zemez\Repayment\Model\EnquiryFactory $enquiryFactory,
        Mail $mail,
        \Zemez\Repayment\Helper\Data $helper,
        RedirectInterface $redirect,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->enquiryFactory = $enquiryFactory;
        $this->redirect = $redirect;
        $this->mail = $mail;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }
    /**
     * Post user question
     *
     * @return Redirect
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            try {
                $request = $this->getRequest();
                $data = $request->getParams();
                switch ($data['term']) {
                    case '26': $data['term'] = '12 months';break;
                    case '39': $data['term'] = '18 months';break;
                    case '52': $data['term'] = '24 months';break;
                }
                $this->sendEmail($data);
                
                $enquiry = $this->enquiryFactory->create();
                

                $data['date'] = date('Y-m-d H:i:s');
                $enquiry->setData($data);
                $enquiry->save();

                return $this->resultRedirectFactory->create()->setPath('repayment-calculator-confirmation');

            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage(
                    __('An error occurred while processing your form. Please try again later.')
                );
            }
        }
        return $this->resultRedirectFactory->create()->setPath($this->redirect->getRefererUrl());
    }

    /**
     * @param array $post Post data from contact form
     * @return void
     */
    private function sendEmail($post)
    {
        $this->mail->send($post);
    }
}
