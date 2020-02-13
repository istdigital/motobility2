<?php

namespace TemplateMonster\ShopByBrand\Controller\Adminhtml\Index;

use TemplateMonster\ShopByBrand\Model\ResourceModel\Brand\CollectionFactory;
use TemplateMonster\ShopByBrand\Model\BrandRepository;
use Magento\Backend\App\Action;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Mass status action.
 *
 * @package TemplateMonster\ShopByBrand\Controller\Adminhtml\Index
 */
abstract class MassStatus extends Action
{
    const ADMIN_RESOURCE = 'TemplateMonster_ShopByBrand::brand_save';

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
     * @var TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * Brand status.
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
     * @param Filter            $filter
     * @param CollectionFactory $collectionFactory
     * @param BrandRepository   $brandRepository
     * @param TypeListInterface $cacheTypeList
     * @param Context           $context
     */
    public function __construct(
        Filter $filter,
        CollectionFactory $collectionFactory,
        BrandRepository $brandRepository,
        TypeListInterface $cacheTypeList,
        Context $context

    ) {
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->_brandRepository = $brandRepository;
        $this->_cacheTypeList = $cacheTypeList;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        foreach ($collection as $brand) {
            /** @var $brand \TemplateMonster\ShopByBrand\Api\Data\BrandInterface */
            $brand->setStatus($this->_status);
            $this->_brandRepository->save($brand);
        }
        $this->_cacheTypeList->invalidate('full_page');

        $this->messageManager->addSuccessMessage(
            __($this->_successMessage, $collection->getSize())
        );

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
