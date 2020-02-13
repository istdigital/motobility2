<?php

namespace TemplateMonster\ShopByBrand\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use TemplateMonster\ShopByBrand\Api\BrandRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class InlineEdit extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'TemplateMonster_ShopByBrand::brand_save';

    /** @var PostDataProcessor */
    protected $dataProcessor;

    protected $_brandRepository;

    /** @var JsonFactory  */
    protected $jsonFactory;

    /**
     * InlineEdit constructor.
     *
     * @param Context                  $context
     * @param BrandRepositoryInterface $brandRepository
     * @param JsonFactory              $jsonFactory
     */
    public function __construct(
        Context $context,
        BrandRepositoryInterface $brandRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->_brandRepository = $brandRepository;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $brandItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($brandItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($brandItems) as $brandId) {
            $brand = $this->_brandRepository->getById($brandId);
            try {
                $extendedPageData = $brand->getData();
                $brand->setData(array_merge($extendedPageData, $brandItems[$brandId]));
                $this->_brandRepository->save($brand);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithPageId($brand, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithPageId($brand, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithPageId(
                    $brand,
                    __('Something went wrong while saving the brand.')
                );
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error,
        ]);
    }
}
