<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Zemez\StockAlert\Controller\Index;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Zemez\StockAlert\Model\Mail;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\DB\Adapter\DuplicateException;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Newsletter\Model\SubscriberFactory;

class Post extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

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

    /**
     * Subscriber factory
     *
     * @var SubscriberFactory
     */
    protected $_subscriberFactory;

    private $alertFactory;

    private $resultJsonFactory;

    private $_date;

    /**
     * @param Context $context
     * @param MailInterface $mail
     * @param DataPersistorInterface $dataPersistor
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Mail $mail,
        DataPersistorInterface $dataPersistor,
        \Zemez\StockAlert\Model\SubscriberFactory $alertFactory,
        SubscriberFactory $subscriberFactory,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->mail = $mail;
        $this->alertFactory = $alertFactory;
        $this->dataPersistor = $dataPersistor;
        $this->resultJsonFactory = $jsonFactory;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_date =  $date;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * Post user question
     *
     * @return Redirect
     */
    public function execute()
    {
        $r = [];
        if (!$this->getRequest()->isPost()) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $resultJson = $this->resultJsonFactory->create();
        try {
            $data = $this->validatedParams();
            //$data['date'] = $this->_date->date()->format('Y-m-d H:i:s');
            $data['date'] = date('Y-m-d H:i:s');

            $alert = $this->alertFactory->create();
            $alert->setData($data);
            $alert->save();
            $this->sendEmail($data);

            //Newsletter subscription
            if(isset($data["is_subscribe"]) && $data["is_subscribe"] == 1){        
                $subscriber = $this->_subscriberFactory->create()->loadByEmail($data['email']);
                if ($subscriber->getId()
                    && (int) $subscriber->getSubscriberStatus() === \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED
                ) {}else{
                    $status = (int) $this->_subscriberFactory->create()->subscribe($data['email']);
                }
            }
            $r = ['error' => false];  
        } catch (LocalizedException $e) {
            $resultJson->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_FORBIDDEN);
            if(preg_match('/constraint violation/', $e->getMessage())){
                $r = ['error' => true,'message' => __('You are already subscribered to this product.')];
            }else{
                $r = ['error' => true,'message' => $e->getMessage()];
            }
        } catch (\Exception $e) {
            $resultJson->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_FORBIDDEN);
            $r = ['error' => true,'message' => __('An error occurred while processing your form. Please try again.')];
        } 
        
        return $resultJson->setData($r);
    }

    /**
     * @param array $post Post data from contact form
     * @return void
     */
    private function sendEmail($post)
    {
        $this->mail->sendToAdmin(
            ['data' => new DataObject($post)]
        );
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function validatedParams()
    {
        $request = $this->getRequest();
        if (trim($request->getParam('name')) === '') {
            throw new LocalizedException(__('Enter the Name and try again.'));
        }
        if (trim($request->getParam('email')) === '') {
            throw new LocalizedException(__('Enter the email and try again.'));
        }

        return $request->getParams();
    }
}
