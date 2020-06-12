<?php
namespace Zendesk\Zendesk\Controller\Adminhtml\Landing;

use Magento\Framework\Exception\NoSuchEntityException;

class Index extends \Magento\Backend\App\AbstractAction
{
    const ADMIN_RESOURCE = 'Zendesk_Zendesk::zendesk';

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $redirectFactory;

    /**
     * Index constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $request = $this->getRequest();
        $redirect = $this->redirectFactory->create();
        try {
            if ($orderId = $request->getParam('order_id')) {
                $redirect->setPath('sales/order/view', ['order_id' => $orderId]);
            } elseif ($orderIncrementId = $this->getRequest()->getParam('order_increment_id')) {
                $order = $this->getOrderByIncrementId($orderIncrementId);
                $redirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
            } elseif ($customerId = $this->getRequest()->getParam('customer_id')) {
                $redirect->setPath('customer/index/edit', ['id' => $customerId]);
            } else {
                throw new \InvalidArgumentException(__('Invalid input parameter'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something was wrong during processing your request. Try again')
            );
            $redirect->setPath($this->_backendUrl->getStartupPageUrl());
        }
        return $redirect;
    }

    /**
     * @param $incrementId
     * @return \Magento\Sales\Api\Data\OrderInterface|mixed
     * @throws NoSuchEntityException
     */
    protected function getOrderByIncrementId($incrementId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId, 'eq')->create();
        $orderList = $this->orderRepository->getList($searchCriteria)->getItems();
        $order = reset($orderList);
        if (!$order) {
            throw new NoSuchEntityException(__('Invalid order increment ID'));
        }
        return reset($orderList);
    }

    /**
     * @return bool
     */
    public function _processUrlKeys()
    {
        if ($this->_auth->isLoggedIn()) {
            return true;
        }
        $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);
        $this->_redirect($this->_backendUrl->getStartupPageUrl());
        return false;
    }
}
