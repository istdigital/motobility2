<?php

namespace TemplateMonster\ShopByBrand\Controller\Ajax;

use TemplateMonster\ShopByBrand\Model\ResourceModel\Brand\CollectionFactory as BrandCollectionFactory;

class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var BrandCollectionFactory
     */
    protected $_brandCollectionFactory;

    protected $_urlInterface;


    /**
     * View constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \TemplateMonster\ShopByBrand\Api\BrandRepositoryInterface $brandRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        BrandCollectionFactory $brandCollectionFactory,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->_brandCollectionFactory = $brandCollectionFactory;
        $this->_urlInterface = $urlInterface;
        parent::__construct($context);
    }

    public function execute()
    {
        $collection = $this->_brandCollectionFactory->create();
        $collection->addWebsiteFilter()->addEnabledFilter();

        $return = [];
        $m = $this->_urlInterface->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]).'logo/logo/';

        foreach ($collection as $brand) {
            $return[] = [
                'name' => $brand->getName(),
                'url_key' => $this->_urlInterface->getUrl($brand->getUrlKey()),
                'logo' => $m.$brand->getLogo(),
            ];            
        }
        echo json_encode($return);
    }
}