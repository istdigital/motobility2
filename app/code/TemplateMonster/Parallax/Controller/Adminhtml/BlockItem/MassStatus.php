<?php

namespace TemplateMonster\Parallax\Controller\Adminhtml\BlockItem;

use TemplateMonster\Parallax\Api\BlockItemRepositoryInterface;
use TemplateMonster\Parallax\Model\ResourceModel\Block\Item\CollectionFactory;
use TemplateMonster\Parallax\Controller\Adminhtml\BlockItem;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;

/**
 * Abstract status mass action.
 */
abstract class MassStatus extends BlockItem
{
    /**
     * @var BlockItemRepositoryInterface
     */
    protected $_blockItemRepository;

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
     * @param BlockItemRepositoryInterface  $blockRepository
     * @param CollectionFactory             $collectionFactory
     * @param Action\Context                $context
     */
    public function __construct(
        BlockItemRepositoryInterface $blockRepository,
        CollectionFactory $collectionFactory,
        Action\Context $context
    ) {
        $this->_blockItemRepository = $blockRepository;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('ids');

        $collection = $this->_collectionFactory->create();
        $collection->addFieldToFilter('item_id', $ids);

        foreach ($collection->getItems() as $blockItem) {
            /** @var $blockItem \TemplateMonster\Parallax\Api\Data\BlockItemInterface */
            $blockItem->setStatus($this->_status);
            $this->_blockItemRepository->save($blockItem);
        }
        $this->messageManager->addSuccessMessage(
            __($this->_successMessage, $collection->getSize())
        );

        /** @var \Magento\Framework\Controller\Result\Redirect $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $result->setRefererOrBaseUrl();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('TemplateMonster_Parallax::item_save');
    }
}
