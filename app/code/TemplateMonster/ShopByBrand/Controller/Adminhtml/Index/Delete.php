<?php

namespace TemplateMonster\ShopByBrand\Controller\Adminhtml\Index;

use TemplateMonster\ShopByBrand\Api\BrandRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager;
/**
 * Delete brand action.
 *
 * @package TemplateMonster\ShopByBrand\Controller\Adminhtml\Index
 */
class Delete extends Action
{
    const ADMIN_RESOURCE = 'TemplateMonster_ShopByBrand::brand_delete';

    /**
     * @var BrandRepositoryInterface
     */
    protected $_brandRepository;

    /**
     * Delete constructor.
     *
     * @param BrandRepositoryInterface $brandRepository
     * @param Action\Context           $context
     */
    public function __construct(
        BrandRepositoryInterface $brandRepository,
        Action\Context $context
    )
    {
        $this->_brandRepository = $brandRepository;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('brand_id');

        try {
            $objectManager = ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();

            $connection->query("DELETE FROM " . $resource->getTableName('eav_attribute_option') . " WHERE `option_id` = $id");

            $this->_brandRepository->deleteById($id);

            $this->messageManager->addSuccessMessage(
                __('Brand has been successfully deleted.')
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
        $resultRedirect->setPath('*/*');

        return $resultRedirect;
    }
}
