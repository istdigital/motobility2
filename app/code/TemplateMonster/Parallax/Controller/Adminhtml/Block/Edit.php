<?php

namespace TemplateMonster\Parallax\Controller\Adminhtml\Block;

use Magento\Framework\Exception\NoSuchEntityException;
use TemplateMonster\Parallax\Api\BlockRepositoryInterface;
use TemplateMonster\Parallax\Controller\Adminhtml\Block;
use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\Controller\ResultFactory;

/**
 * Edit block action.
 */
class Edit extends Block
{
    /**
     * @var BlockRepositoryInterface
     */
    protected $_blockRepository;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * Edit constructor.
     *
     * @param BlockRepositoryInterface $blockRepository
     * @param Registry                 $registry
     * @param Action\Context           $context
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        Registry $registry,
        Action\Context $context
    ) {
        $this->_blockRepository = $blockRepository;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $block = $this->_getModel();
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('Block not found.'));
            /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            return $resultRedirect->setPath('*/*/');
        }
        if ($data = $this->_getSession()->getFormData()) {
            $block->setData($data);
        }
        $this->_coreRegistry->register('current_parallax_block', $block);

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
            ->getConfig()->getTitle()->prepend($this->_getModel()->getId() ? $this->_getModel()->getName() : 'New Block')
        ;

        return $resultPage;
    }

    /**
     *
     * @return mixed
     */
    protected function _getModel()
    {
        $id = $this->getRequest()->getParam('block_id');

        return $id ? $this->_blockRepository->getById($id)
            : $this->_blockRepository->getModelInstance();
    }
}
