<?php

namespace TemplateMonster\Parallax\Controller\Adminhtml\BlockItem;

use TemplateMonster\Parallax\Api\BlockItemRepositoryInterface;
use TemplateMonster\Parallax\Model\ResourceModel\Block\Item\CollectionFactory;
use TemplateMonster\Parallax\Controller\Adminhtml\BlockItem;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;

/**
 * Block item delete mass action.
 */
class MassDelete extends BlockItem
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
     * MassDelete constructor.
     *
     * @param BlockItemRepositoryInterface $blockItemRepository
     * @param CollectionFactory            $collectionFactory
     * @param Action\Context               $context
     */
    public function __construct(
        BlockItemRepositoryInterface $blockItemRepository,
        CollectionFactory $collectionFactory,
        Action\Context $context
    ) {
        $this->_blockItemRepository = $blockItemRepository;
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
            $this->_blockItemRepository->delete($blockItem);
        }
        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been deleted.', count($collection))
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
        return $this->_authorization->isAllowed('TemplateMonster_Parallax:item_delete');
    }
}
