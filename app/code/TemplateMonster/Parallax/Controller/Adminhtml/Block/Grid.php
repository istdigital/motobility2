<?php

namespace TemplateMonster\Parallax\Controller\Adminhtml\Block;

use TemplateMonster\Parallax\Api\BlockRepositoryInterface;
use TemplateMonster\Parallax\Controller\Adminhtml\Block;
use Magento\Framework\View\LayoutFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\Controller\ResultFactory;

/**
 * BlockItems grid action.
 */
class Grid extends Block
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
     * @var LayoutFactory
     */
    protected $_layoutFactory;

    /**
     * Grid constructor.
     *
     * @param BlockRepositoryInterface $blockRepository
     * @param Registry                 $coreRegistry
     * @param LayoutFactory            $layoutFactory
     * @param Action\Context           $context
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        Registry $coreRegistry,
        LayoutFactory $layoutFactory,
        Action\Context $context
    )
    {
        $this->_blockRepository = $blockRepository;
        $this->_coreRegistry = $coreRegistry;
        $this->_layoutFactory = $layoutFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('block_id');

        try {
            $block = $this->_blockRepository->getById($id);
            $this->_coreRegistry->register('current_parallax_block', $block);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('404: block not found.'));

            /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index');
        }

        $layout = $this->_layoutFactory->create();
        $block = $layout->createBlock('TemplateMonster\Parallax\Block\Adminhtml\Block\Edit\Tab\Item', 'item_table');

        /** @var \Magento\Framework\Controller\Result\Raw $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setContents($block->toHtml());

        return $result;
    }
}
