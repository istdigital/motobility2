<?php

namespace TemplateMonster\Parallax\Controller\Adminhtml\Block;

use Magento\Framework\Exception\LocalizedException;
use TemplateMonster\Parallax\Api\BlockRepositoryInterface;
use TemplateMonster\Parallax\Controller\Adminhtml\Block;
use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\Controller\ResultFactory;

/**
 * Delete block action.
 */
class Delete extends Block
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
     * Delete constructor.
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
        $id = $this->getRequest()->getParam('block_id');

        try {
            $this->_blockRepository->deleteById($id);
            $this->messageManager->addSuccessMessage(
                __('Block has been successfully deleted.')
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('There is an unknown error occurred while deleting the item.')
            );
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setRefererOrBaseUrl();
    }
}
