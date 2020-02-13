<?php

namespace TemplateMonster\ShopByBrand\Controller\Adminhtml\Index;

use TemplateMonster\ShopByBrand\Api\BrandRepositoryInterface;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry as CoreRegistry;

/**
 * Brand edit action.
 *
 * @package TemplateMonster\ShopByBrand\Controller\Adminhtml\Index
 */
class Edit extends Action
{
    const ADMIN_RESOURCE = 'TemplateMonster_ShopByBrand::brand_save';

    /**
     * @var BrandRepositoryInterface
     */
    protected $_brandRepository;

    /**
     * @var CoreRegistry
     */
    protected $_coreRegistry;

    public function __construct(
        BrandRepositoryInterface $brandRepository,
        CoreRegistry $coreRegistry,
        Context $context
    )
    {
        $this->_brandRepository = $brandRepository;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('brand_id');

        if ($id) {
            try {
                $model = $this->_brandRepository->getById($id);
                $this->_coreRegistry->register('brand', $model);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This brand no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        // 5. Build edit form
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Brand') : __('New Brand'),
            $id ? __('Edit Brand') : __('New Brand')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Brand'));
        if (isset($model)) {
            $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getTitle() : __('New Brand'));
        }

        return $resultPage;
    }

    /**
     * Init page.
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('TemplateMonster_ShopByBrand::brand')
            ->addBreadcrumb(__('Brand'), __('Brand'))
            ->addBreadcrumb(__('Brand'), __('Brand'));

        return $resultPage;
    }
}
