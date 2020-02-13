<?php

namespace TemplateMonster\Parallax\Controller\Adminhtml\Block;

use TemplateMonster\Parallax\Api\BlockRepositoryInterface;
use TemplateMonster\Parallax\Controller\Adminhtml\Block;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action;

/**
 * Class Save.
 */
class Save extends Block
{
    /**
     * @var BlockRepositoryInterface
     */
    protected $_blockRepository;

    /**
     * Save constructor.
     *
     * @param BlockRepositoryInterface $blockRepository
     * @param Action\Context           $context
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        Action\Context $context
    ) {
        $this->_blockRepository = $blockRepository;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue('block');
        if ($data) {
            $model = $this->_initModel();
            $model->addData($data);

            try {
                $this->_blockRepository->save($model);
                $this->messageManager->addSuccessMessage(__('The block has been successfully saved.'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['block_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the block.'));
            }
            $this->_getSession()->setFormData($data);

            return $resultRedirect->setPath('*/*/edit', ['block_id' => $model->getId()]);
        }

        return $resultRedirect->setPath('*/*');
    }

    /**
     * Init model
     *
     * @param int|null $id
     *
     * @return \TemplateMonster\Parallax\Api\Data\BlockInterface
     */
    protected function _initModel($id = null)
    {
        $id = $id ?: $this->getRequest()->getParam('block_id');

        return $id
            ? $this->_blockRepository->getById($id)
            : $this->_blockRepository->getModelInstance();
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('TemplateMonster_Parallax::block_save');
    }
}
