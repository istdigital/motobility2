<?php

namespace TemplateMonster\Parallax\Controller\Adminhtml\Block;

use TemplateMonster\Parallax\Api\BlockRepositoryInterface;
use TemplateMonster\Parallax\Model\ResourceModel\Block\CollectionFactory;
use TemplateMonster\Parallax\Controller\Adminhtml\Block;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;

/**
 * Abstract status mass action.
 */
abstract class MassStatus extends Block
{
    /**
     * @var BlockRepositoryInterface
     */
    protected $_blockRepository;

    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Parallax block status.
     *
     * @var int
     */
    protected $_status;

    /**
     * Action success message.
     *
     * @var string
     */
    protected $_successMessage;

    /**
     * MassStatus constructor.
     *
     * @param BlockRepositoryInterface  $blockRepository
     * @param Filter                    $filter
     * @param CollectionFactory         $collectionFactory
     * @param Action\Context            $context
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Action\Context $context
    ) {
        $this->_blockRepository = $blockRepository;
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        foreach ($collection->getItems() as $block) {
            /** @var $block \TemplateMonster\Parallax\Api\Data\BlockInterface */
            $block->setStatus($this->_status);
            $this->_blockRepository->save($block);

        }
        $this->messageManager->addSuccessMessage(
            __($this->_successMessage, $collection->getSize())
        );

        /** @var \Magento\Framework\Controller\Result\Redirect $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $result->setPath('*/*/');

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('TemplateMonster_Parallax::block_save');
    }
}
