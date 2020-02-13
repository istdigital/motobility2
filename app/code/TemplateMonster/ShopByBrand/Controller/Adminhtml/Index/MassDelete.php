<?php

namespace TemplateMonster\ShopByBrand\Controller\Adminhtml\Index;

use TemplateMonster\ShopByBrand\Model\ResourceModel\Brand\CollectionFactory;
use TemplateMonster\ShopByBrand\Model\BrandRepository;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\App\ObjectManager;
/**
 * Mass delete action.
 *
 * @package TemplateMonster\ShopByBrand\Controller\Adminhtml\Index
 */
class MassDelete extends Action
{
    const ADMIN_RESOURCE = 'TemplateMonster_ShopByBrand::brand_delete';

    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var BrandRepository
     */
    protected $_brandRepository;

    /**
     * MassDelete constructor.
     *
     * @param Context           $context
     * @param Filter            $filter
     * @param CollectionFactory $collectionFactory
     * @param BrandRepository   $brandRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        BrandRepository $brandRepository
    ) {
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->_brandRepository = $brandRepository;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*');

        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        try {
            $objectManager = ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();

            foreach ($collection as $brand) {
                $id = $brand->getBrandId();
                $connection->query("DELETE FROM " . $resource->getTableName('eav_attribute_option') . " WHERE `option_id` = $id");
                $this->_brandRepository->delete($brand);
            }
        } catch (CouldNotDeleteException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultRedirect;
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been deleted.', $collection->getSize())
        );

        return $resultRedirect;
    }
}
