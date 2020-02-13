<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Zemez\StockAlert\Controller\Adminhtml\Subscriber;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Zemez\StockAlert\Model\ResourceModel\Subscriber\CollectionFactory;
use Zemez\StockAlert\Model\Mail;
use Magento\Framework\DataObject;
/**
 * Class MassDelete
 */
class massNotified extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Zemez_StockAlert::config_stockalert';

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var MailInterface
     */
    private $mail;

    private $_date;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(Context $context, 
        Mail $mail, 
        Filter $filter, 
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        CollectionFactory $collectionFactory)
    {
        $this->filter = $filter;
        $this->mail = $mail;
        $this->_date =  $date;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection as $subscriber) {
            $this->mail->sendToUser(
                ['data' => new DataObject($subscriber->getData())]
            );
            $subscriber->setStatus('notified')
            ->setNotifiedAt(date('Y-m-d H:i:s'))
            ->save();
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been notified.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
