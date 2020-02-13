<?php

namespace TemplateMonster\Parallax\Controller\Adminhtml\Block;

use TemplateMonster\Parallax\Model\ResourceModel\Block\CollectionFactory;
use TemplateMonster\Parallax\Controller\Adminhtml\Block;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;

/**
 * Block delete mass action.
 */
class MassDelete extends Block
{
    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * MassDelete constructor.
     *
     * @param Filter            $filter
     * @param CollectionFactory $collectionFactory
     * @param Action\Context    $context
     */
    public function __construct(
        Filter $filter,
        CollectionFactory $collectionFactory,
        Action\Context $context
    ) {
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
            $block->delete();
        }
        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been deleted.', count($collection))
        );

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('TemplateMonster_Parallax::block_delete');
    }
}
