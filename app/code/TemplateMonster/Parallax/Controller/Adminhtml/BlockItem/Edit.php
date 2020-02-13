<?php

namespace TemplateMonster\Parallax\Controller\Adminhtml\BlockItem;

use TemplateMonster\Parallax\Api\BlockItemRepositoryInterface;
use TemplateMonster\Parallax\Controller\Adminhtml\BlockItem;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Edit.
 */
class Edit extends BlockItem
{
    /**
     * @var BlockItemRepositoryInterface
     */
    protected $_blockItemRepository;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * Edit constructor.
     *
     * @param BlockItemRepositoryInterface $blockRepository
     * @param Registry                     $registry
     * @param Action\Context               $context
     */
    public function __construct(
        BlockItemRepositoryInterface $blockRepository,
        Registry $registry,
        Action\Context $context
    ) {
        $this->_blockItemRepository = $blockRepository;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $blockItem = $this->_getModel();
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('Block item not found.'));
            /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            return $resultRedirect->setPath('*/*/');
        }
        if ($data = $this->_getSession()->getFormData()) {
            $blockItem->setData($data);
        }
        $this->_coreRegistry->register('current_parallax_block_item', $blockItem);

        $resultPage = $this->_initAction();

        return $resultPage;
    }

    /**
     * @inheritdoc
     */
    protected function _initAction()
    {
        $resultPage = parent::_initAction();
        $resultPage
            ->setActiveMenu('TemplateMonster_Parallax::parallax_block')
            ->addBreadcrumb(__('Edit Parallax Blocks'), __('Edit Parallax Blocks'))
            ->getConfig()->getTitle()->prepend($this->_getModel()->getId() ? $this->_getModel()->getName() : 'New Block Item')
        ;

        return $resultPage;
    }

    /**
     *
     * @return mixed
     */
    protected function _getModel()
    {
        $id = $this->getRequest()->getParam('item_id');

        return $id ? $this->_blockItemRepository->getById($id)
            : $this->_blockItemRepository->getModelInstance();
    }
}
