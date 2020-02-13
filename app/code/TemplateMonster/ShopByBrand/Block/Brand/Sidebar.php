<?php

namespace TemplateMonster\ShopByBrand\Block\Brand;

use TemplateMonster\ShopByBrand\Helper\Data as ShopByBrandHelper;
use TemplateMonster\ShopByBrand\Model\ResourceModel\Brand\CollectionFactory as BrandCollectionFactory;
use \Magento\Framework\Image\AdapterFactory;
use Magento\Framework\View\Element\Template;

/**
 * Brand sidebar block.
 *
 * @package TemplateMonster\ShopByBrand\Block\Brand
 */
class Sidebar extends Template
{
    /**
     * @var int
     */
    const DEFAULT_BRAND_LIMIT = 10;

    /**
     * @var ShopByBrandHelper
     */
    protected $_helper;

    /**
     * @var BrandCollectionFactory
     */
    protected $_brandCollectionFactory;

    /**
     * @var ImageFactory
     */
    protected $_imageFactory;


    /**
     * Sidebar constructor.
     *
     * @param ShopByBrandHelper      $helper
     * @param BrandCollectionFactory $brandCollectionFactory
     * @param AdapterFactory           $imageFactory
     * @param Template\Context       $context
     * @param array                  $data
     */
    public function __construct(
        ShopByBrandHelper $helper,
        BrandCollectionFactory $brandCollectionFactory,
        AdapterFactory $imageFactory,
        Template\Context $context,
        array $data = []
    )
    {
        $this->_helper = $helper;
        $this->_brandCollectionFactory = $brandCollectionFactory;
        $this->_imageFactory = $imageFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get brand collection.
     *
     * @return \TemplateMonster\ShopByBrand\Model\ResourceModel\Brand\Collection|\TemplateMonster\ShopByBrand\Model\Brand[]
     */
    public function getBrandCollection()
    {
        $limit = $this->_helper->getSidebarBrandLimit() ?: self::DEFAULT_BRAND_LIMIT;

        /** @var \TemplateMonster\ShopByBrand\Model\ResourceModel\Brand\Collection $collection */
        $collection = $this->_brandCollectionFactory->create();
        $collection->addWebsiteFilter()->addEnabledFilter()->setPageSize($limit);

        return $collection;
    }

    public function isShowSidebarLogo()
    {
        return $this->_helper->isShowSidebarLogo();
    }

    public function isShowSidebarName()
    {
        return $this->_helper->isShowSidebarName();
    }

    /**
     * Get all brands url.
     *
     * @return string
     */
    public function getAllBrandsUrl()
    {
        return $this->_urlBuilder->getUrl('brand');
    }

    /**
     * Get image.
     *
     * @return string
     */
    public function getImage($url)
    {
        return $this->_imageFactory->create([
            'data' => [
                'template' => 'Magento_Catalog::product/image.phtml',
                'image_url' => $url,
                'width' => 95,
                'height' => 90,
            ]
        ]);

    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->_helper->isEnabled()) {
            return '';
        }
        if (!$this->_helper->isSidebarEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}