<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Zemez\Contact\Controller\Index;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Contact\Model\ConfigInterface;
use Magento\Contact\Model\MailInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;

class Post extends \Magento\Contact\Controller\Index implements HttpPostActionInterface
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
     * @var LoggerInterface
     */
    private $zmail;

    private $serverkey;

    private $contactFactory;

    /**
     * @param Context $context
     * @param ConfigInterface $contactsConfig
     * @param MailInterface $mail
     * @param DataPersistorInterface $dataPersistor
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        \Zemez\Contact\Model\ContactFactory $contactFactory,
        ConfigInterface $contactsConfig,
        MailInterface $mail,
        \Zemez\Contact\Model\Mail $zmail,
        \Zemez\Contact\Helper\Data $helper,
        DataPersistorInterface $dataPersistor,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context, $contactsConfig);
        $this->context = $context;
        $this->contactFactory = $contactFactory;
        $this->mail = $mail;
        $this->zmail = $zmail;
        $this->dataPersistor = $dataPersistor;
        $this->serverkey = $helper->getServerKey();
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }
    /**
     * Post user question
     *
     * @return Redirect
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        try {
            $data = $this->validatedParams();
            $this->sendEmail($data);
            
            $contact = $this->contactFactory->create();
            $data['date'] = date('Y-m-d H:i:s');
            $data['message'] = $data['comment'];
            $data['phone'] = $data['telephone'];
            $contact->setData($data);
            $contact->save();

            $this->dataPersistor->clear('contact_us');

            return $this->resultRedirectFactory->create()->setPath('contact-thankyou');

        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->dataPersistor->set('contact_us', $this->getRequest()->getParams());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('An error occurred while processing your form. Please try again later.'.$e->getMessage())
            );
            $this->dataPersistor->set('contact_us', $this->getRequest()->getParams());
        }
        return $this->resultRedirectFactory->create()->setPath('contact/index');
    }

    /**
     * @param array $post Post data from contact form
     * @return void
     */
    private function sendEmail($post)
    {
        $this->mail->send(
            $post['email'],
            ['data' => new DataObject($post)]
        );
        $this->zmail->send(
            $post['email'],
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
        if (trim($request->getParam('comment')) === '') {
            throw new LocalizedException(__('Enter the comment and try again.'));
        }
        if (false === \strpos($request->getParam('email'), '@')) {
            throw new LocalizedException(__('The email address is invalid. Verify the email address and try again.'));
        }
        if (trim($request->getParam('hideit')) !== '') {
            throw new \Exception();
        }
        
        return $request->getParams();
    }
}
