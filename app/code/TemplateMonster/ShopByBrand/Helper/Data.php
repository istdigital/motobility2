<?php

namespace TemplateMonster\ShopByBrand\Helper;

use TemplateMonster\ShopByBrand\Model\ResourceModel\Brand as Resource;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    protected $_resource;

    /**
     * @var ImageFactory
     */
    protected $_imageFactory;

    /**
     * @var FileSystem
     */
    protected $_filesystem;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    const XML_PATH_LISTING_SHOW_NAME = 'shopbybrand/brand_listing/show_name';

    const XML_PATH_LISTING_SHOW_LOGO = 'shopbybrand/brand_listing/show_logo';

    const XML_PATH_LISTING_SHOW_SHORT_DESCRIPTION = 'shopbybrand/brand_listing/show_short_description';

    const XML_PATH_SIDEBAR_ENABLED = 'shopbybrand/brand_sidebar/enabled';

    const XML_PATH_SIDEBAR_SHOW_LOGO = 'shopbybrand/brand_sidebar/show_logo';

    const XML_PATH_SIDEBAR_SHOW_NAME = 'shopbybrand/brand_sidebar/show_name';

    const XML_PATH_SIDEBAR_BRAND_LIMIT = 'shopbybrand/brand_sidebar/quantity';


    public function __construct(
        Context $context,
        Resource $resource,
        AdapterFactory $imageFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    )
    {
        $this->_resource = $resource;
        $this->_imageFactory = $imageFactory;
        $this->_filesystem = $filesystem;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function isEnabled($store = null)
    {
       return $this->scopeConfig->isSetFlag('shopbybrand/general/enabled', ScopeInterface::SCOPE_STORE, $store);
    }

    public function isShowTopLink($store = null)
    {
        return $this->scopeConfig->isSetFlag('shopbybrand/general/show_top_link', ScopeInterface::SCOPE_STORE, $store);
    }

    public function topLinkNumber($store = null)
    {
        return $this->scopeConfig->getValue('shopbybrand/general/number', ScopeInterface::SCOPE_STORE, $store);
    }

    public function getBrandLayout($store = null)
    {
        return $this->scopeConfig->getValue('shopbybrand/brand/layout', ScopeInterface::SCOPE_STORE, $store);
    }

    public function isShowBrandBanner($store = null)
    {
        return $this->scopeConfig->isSetFlag('shopbybrand/brand/show_banner', ScopeInterface::SCOPE_STORE, $store);
    }
    public function isShowBrandLogo($store = null)
    {
        return $this->scopeConfig->isSetFlag('shopbybrand/brand/show_logo', ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Check is show brand description.
     *
     * @param string|null $store
     *
     * @return bool
     */
    public function isShowBrandDescription($store = null)
    {
        return $this->scopeConfig->isSetFlag('shopbybrand/brand/show_description', ScopeInterface::SCOPE_STORE, $store);
    }

    public function isProductShowLogo($store = null)
    {
        return $this->scopeConfig->isSetFlag('shopbybrand/product/show_logo', ScopeInterface::SCOPE_STORE, $store);
    }

    public function isProductShowName($store = null)
    {
        return $this->scopeConfig->isSetFlag('shopbybrand/product/show_name', ScopeInterface::SCOPE_STORE, $store);
    }

    public function getProductPageLogoWidth($store = null)
    {
        return $this->scopeConfig->getValue('shopbybrand/product/brand_logo_width', ScopeInterface::SCOPE_STORE, $store);
    }

    public function getProductPageSelector($store = null)
    {
        return $this->scopeConfig->getValue('shopbybrand/product/selector', ScopeInterface::SCOPE_STORE, $store);
    }

    public function isShowListingName($store = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_LISTING_SHOW_NAME, ScopeInterface::SCOPE_STORE, $store);
    }
    public function isShowListingLogo($store = null)
    {
       return $this->scopeConfig->isSetFlag(self::XML_PATH_LISTING_SHOW_LOGO, ScopeInterface::SCOPE_STORE, $store);
    }

    public function isShowListingShortDescription($store = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_LISTING_SHOW_SHORT_DESCRIPTION, ScopeInterface::SCOPE_STORE, $store);
    }

    public function isSidebarEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SIDEBAR_ENABLED, ScopeInterface::SCOPE_STORE, $store);
    }

    public function isShowSidebarLogo($store = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SIDEBAR_SHOW_LOGO, ScopeInterface::SCOPE_STORE, $store);
    }

    public function isShowSidebarName($store = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SIDEBAR_SHOW_NAME, ScopeInterface::SCOPE_STORE, $store);
    }

    public function getSidebarBrandLimit($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SIDEBAR_BRAND_LIMIT, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Check is product associated with with a brand.
     *
     * @param Product $product
     *
     * @return int|null
     */
    public function isAssignedToBrand(Product $product)
    {
        if ($product->getData('brand_id')) {
            return $product->getData('brand_id');
        }

        return $this->_resource->getBrandByProduct($product);
    }

    public function getBrandById($brandId)
    {
        return $this->_resource->getBrandById($brandId);
    }

    

    /**
     * Get resize image url .
     *
     * @return string
     */
    public function resizeImageUrl($image, $width = null, $height = null, $type = 'logo')
    {
        if(empty($image)) {
            return false;
        }
        $path = 'logo/logo/';
        if ($type == 'banner') {
            $path = 'brandpage/brandpage/';
        } elseif ($type == 'product-logo') {
            $path = 'brandproductpage/brandproductpage/';
        }
        $absolutePath = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath($path).$image;
        $imageResized = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath($path . 'resized/'.$width.'/').$image;
        //create image factory...
        $imageResize = $this->_imageFactory->create();
        $imageResize->open($absolutePath);
        $imageResize->constrainOnly(TRUE);
        $imageResize->keepTransparency(TRUE);
        $imageResize->keepFrame(FALSE);
        $imageResize->keepAspectRatio(TRUE);
        $imageResize->resize($width,$height);
        //destination folder
        $destination = $imageResized ;
        //save image
        $imageResize->save($destination);

        $resizedURL = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$path.'resized/'.$width.'/'.$image;
        return $resizedURL;
    }

}