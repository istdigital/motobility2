<?php

namespace Cryozonic\StripePayments\Controller\Webhooks;

use Cryozonic\StripePayments\Helper\Logger;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Cryozonic\StripePayments\Helper\Generic $helper,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $dbTransaction,
        \Cryozonic\StripePayments\Helper\Webhooks $webhooks
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);

        $this->helper = $helper;
        $this->webhooks = $webhooks;
        $this->invoiceService = $invoiceService;
        $this->dbTransaction = $dbTransaction;
    }

    public function execute()
    {
        $this->webhooks->dispatchEvent();
    }
}
