<?php
/**
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace TemplateMonster\ShopByBrand\Controller\Adminhtml\Index;

use Magento\Framework\Exception\LocalizedException;
use TemplateMonster\ShopByBrand\Api\Data\BrandInterface;
use Magento\Framework\App\ObjectManager;

class Save extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'TemplateMonster_ShopByBrand::brand_save';

    /**
     * @var \TemplateMonster\ShopByBrand\Api\Data\BrandInterfaceFactory
     */
    protected $_brand;

    /**
     * @var \TemplateMonster\ShopByBrand\Model\BrandRepository
     */
    protected $_brandRepository;

    /**
     * @var DataPersistorInterface
     */
    protected $_dataPersistor;
    /**
     * @var \Magento\Catalog\Model\ImageUploaderFactory
     */
    protected $_imageUploadLogo;

    /**
     * @var \Magento\Catalog\Model\ImageUploader
     */
    protected $_imageUploadBrand;

    /**
     * @var \Magento\Catalog\Model\ImageUploader
     */
    protected $_imageUploadProduct;

    protected $_cacheTypeList;

    protected $_imagesFieldName = [BrandInterface::LOGO, BrandInterface::BRAND_BANNER, BrandInterface::PRODUCT_BANNER];

    public function __construct(
        \TemplateMonster\ShopByBrand\Api\Data\BrandInterfaceFactory $brand,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \TemplateMonster\ShopByBrand\Model\BrandRepository $brandRepository,
        \Magento\Catalog\Model\ImageUploader $imageUploaderLogo,
        \Magento\Catalog\Model\ImageUploader $imageUploaderBrand,
        \Magento\Catalog\Model\ImageUploader $imageUploaderProduct,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->_brand = $brand;
        $this->_dataPersistor = $dataPersistor;
        $this->_brandRepository = $brandRepository;
        $this->_imageUploadLogo = $imageUploaderLogo;
        $this->_imageUploadBrand = $imageUploaderBrand;
        $this->_imageUploadProduct = $imageUploaderProduct;
        $this->_cacheTypeList = $cacheTypeList;

        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {

            $objectManager = ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();

            $model = $this->_brand->create();
            $id = $this->getRequest()->getParam('brand_id');
            if ($id) {
                $model->load($id);

                if($data['name'] != $model->getName()){
                    $sql = "UPDATE " . $resource->getTableName('eav_attribute_option_value') . " SET `value` = '{$data['name']}' WHERE `option_id` = $id";
                    $connection->query($sql);
                }
            }
            //Prepare image to save
            $data = $this->imagePreprocessing($data);
            $data = $this->_filterCategoryPostData($data);

            if (empty($data['brand_id'])) {
                $sql = "Insert Into " . $resource->getTableName('eav_attribute_option') . " (attribute_id,sort_order) Values (151,0)";
                $connection->query($sql);
                $id =$connection->lastInsertId();
                $sql = "Insert Into " . $resource->getTableName('eav_attribute_option_value') . " (option_id,store_id,value) Values ($id,0,'{$data['name']}')";
                $connection->query($sql);

                $data['brand_id'] = $id;
            }

            $model->addData($data);
            $this->_eventManager->dispatch(
                'brand_prepare_save',
                ['brand' => $model, 'request' => $this->getRequest()]
            );

            try {
                //$model->save();
                $this->_brandRepository->save($model);
                $this->_cacheTypeList->invalidate('full_page');
                $this->messageManager->addSuccessMessage(__('You saved the brand.'));
                $this->_dataPersistor->clear('brand');

                if (isset($data['logo']) && $model->getLogo()) {
                    $this->_imageUploadLogo->moveFileFromTmp($model->getLogo());
                }

                if (isset($data['brand_banner']) && $model->getBrandBanner()) {
                    $this->_imageUploadBrand->moveFileFromTmp($model->getBrandBanner());
                }

                if (isset($data['product_banner']) && $model->getProductBanner()) {
                    $this->_imageUploadProduct->moveFileFromTmp($model->getProductBanner());
                }

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['brand_id' => $model->getId(), '_current' => true]);
                }

                return $resultRedirect->setPath('brand/index/index');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the brand.'));
            }

            $this->_dataPersistor->set('brand', $data);

            return $resultRedirect->setPath('*/*/edit', ['brand_id' => $this->getRequest()->getParam('brand_id')]);
        }

        return $resultRedirect->setPath('brand/index/index');
    }

    protected function _filterCategoryPostData(array $rawData)
    {
        $data = $rawData;
        foreach ($this->_imagesFieldName as $key) {
            // @todo It is a workaround to prevent saving this data in category model and it has to be refactored in future
            if (isset($data[$key]) && is_array($data[$key])) {
                if (!empty($data[$key]['delete'])) {
                    $data[$key] = null;
                } else {
                    if (isset($data[$key][0]['name']) && isset($data[$key][0]['tmp_name'])) {
                        $data[$key] = $data[$key][0]['name'];
                    } else {
                        unset($data[$key]);
                    }
                }
            }
        }

        return $data;
    }

    public function imagePreprocessing($data)
    {
        foreach ($this->_imagesFieldName as $key) {
            if (empty($data[$key])) {
                unset($data[$key]);
                $data[$key]['delete'] = true;
            }
        }

        return $data;
    }
}
