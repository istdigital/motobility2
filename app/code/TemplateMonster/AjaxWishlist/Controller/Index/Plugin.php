<?php

namespace TemplateMonster\AjaxWishlist\Controller\Index;

use TemplateMonster\AjaxWishlist\Helper\Data as AjaxWishlistHelper;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Wishlist\Controller\AbstractIndex as WishlistAction;
use Magento\Wishlist\Controller\Index\Add;
use Magento\Framework\Message\ManagerInterface;

/**
 * Wishlist add action plugin class.
 *
 * @package TemplateMonster\AjaxWishlist\Controller\Index
 */
class Plugin
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var AjaxWishlistHelper
     */
    protected $ajaxWishlistHelper;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * Plugin constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param JsonHelper                 $jsonHelper
     * @param AjaxWishlistHelper         $ajaxWishlistHelper
     * @param CustomerSession            $customerSession
     * @param ManagerInterface           $messageManager
     * @param PageFactory                $pageFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        JsonHelper $jsonHelper,
        AjaxWishlistHelper $ajaxWishlistHelper,
        CustomerSession $customerSession,
        ManagerInterface $messageManager,
        PageFactory $pageFactory
    )
    {
        $this->productRepository = $productRepository;
        $this->jsonHelper = $jsonHelper;
        $this->ajaxWishlistHelper = $ajaxWishlistHelper;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
        $this->pageFactory = $pageFactory;
    }

    /**
     * Before dispatch event handler
     *
     * @param WishlistAction   $subject
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\Response\Http
     */
    public function beforeDispatch(WishlistAction $subject, RequestInterface $request)
    {
        if (!$this->ajaxWishlistHelper->isEnabled()) {
            return;
        }

        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $subject->getResponse();
        if ($request->isAjax() && !$this->customerSession->isLoggedIn()) {
            // cancel redirect that was sent by wishlist beforeDispatch plugin
            if ($response->isRedirect()) {
                $response
                    ->clearHeader('Location')
                    ->setStatusCode(200);
            }

            $response->representJson(
                $this->jsonHelper->jsonEncode(
                    [
                        'success' => false,
                        'error'   => 'not_logged_in'
                    ]
                )
            );
        }
    }

    /**
     * After wishlist execute action plugin.
     *
     * @param WishlistAction $subject
     * @param mixed $result
     *
     * @return mixed
     */
    public function afterExecute(WishlistAction $subject, $result)
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $subject->getRequest();
        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $subject->getResponse();

        if (!$this->ajaxWishlistHelper->isEnabled() || !$request->isAjax()) {
            return $result;
        }

        $data = ['success' => true];
        if ($block = $this->getWishlistBlock()) {
            $data['wishlist'] = $block->toHtml();
        }
        if ($subject instanceof Add) {
            $id = (int) $request->getParam('product');
            $product = $this->productRepository->getById($id);
            $data['message'] = $this->getSuccessMessage($product);
        }

        // flush all messages that were added by action
        $this->messageManager->getMessages(true);

        return $response->representJson(
            $this->jsonHelper->jsonEncode($data)
        );
    }

    /**
     * Get wishlist block
     *
     * @return bool|\Magento\Framework\View\Element\AbstractBlock
     */
    protected function getWishlistBlock()
    {
        $page = $this->pageFactory->create();
        $page->addHandle('wishlist_index_index');

        return $page->getLayout()->getBlock('customer.wishlist');
    }

    /**
     * Get success message.
     *
     * @param ProductInterface $product
     *
     * @return string
     */
    protected function getSuccessMessage(ProductInterface $product)
    {
        return str_replace(
            '{product.name}',
            sprintf('<strong>%s</strong>', $product->getName()),
            $this->ajaxWishlistHelper->getSuccessMessageText()
        );
    }
}