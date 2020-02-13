<?php

namespace TemplateMonster\ShopByBrand\Model;

use TemplateMonster\ShopByBrand\Api\Data\BrandInterface;
use TemplateMonster\ShopByBrand\Helper\Data as ShopByBrandHelper;;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\UrlInterface;

class Brand extends AbstractModel implements BrandInterface, IdentityInterface
{
    const CACHE_TAG = 'brand';

    /**
     * @var string
     */
    protected $_cacheTag = 'brand';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'brand';

    /**
     * @var \TemplateMonster\ShopByBrand\Helper\Data
     */
    protected $_helper;

    /**
     * @var
     */
    protected $_urlBuilder;

    /**
     * Brand constructor.
     *
     * @param ShopByBrandHelper     $helper
     * @param Context               $context
     * @param Registry              $registry
     * @param UrlInterface          $urlBuilder
     * @param AbstractResource|null $resource
     * @param AbstractDb|null       $resourceCollection
     * @param array                 $data
     */
    public function __construct(
        ShopByBrandHelper $helper,
        Context $context,
        Registry $registry,
        UrlInterface $urlBuilder,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->_helper = $helper;
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('TemplateMonster\ShopByBrand\Model\ResourceModel\Brand');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    public function getUrl()
    {
        return $this->_urlBuilder->getUrl($this->getUrlPage());
    }

    public function getAssignedProductIds()
    {
        return $this->_getResource()->getAssignedProductIds($this);
    }

    public function getName()
    {
        return $this->getData(self::NAME);
    }

    public function getUrlPage()
    {
        return $this->getData(self::URL_PAGE);
    }

    public function getPageTitle()
    {
        return $this->getData(self::PAGE_TITLE);
    }

    public function getLogo()
    {
        return $this->getData(self::LOGO);
    }

    public function getBrandBanner()
    {
        return $this->getData(self::BRAND_BANNER);
    }

    public function getProductBanner()
    {
        return $this->getData(self::PRODUCT_BANNER);
    }

    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    public function getFeatured()
    {
        return $this->getData(self::FEATURED);
    }

    public function getShortDescription()
    {
        return $this->getData(self::SHORT_DESCRIPTION);
    }

    public function getMainDescription()
    {
        return $this->getData(self::MAIN_DESCRIPTION);
    }

    public function getMetaKeywords()
    {
        return $this->getData(self::META_KEYWORDS);
    }

    public function getMetaDescription()
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    public function setId($id)
    {
        return $this->setData(self::BRAND_ID, $id);
    }

    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    public function setUrlPage($urlPage)
    {
        return $this->setData(self::URL_PAGE, $urlPage);
    }

    public function setPageTitle($pageTitle)
    {
        return $this->setData(self::PAGE_TITLE, $pageTitle);
    }

    public function setLogo($logo)
    {
        return $this->setData(self::LOGO, $logo);
    }

    public function setBrandBanner($brandBanner)
    {
        return $this->setData(self::BRAND_BANNER, $brandBanner);
    }

    public function setProductBanner($productBanner)
    {
        return $this->setData(self::PRODUCT_BANNER, $productBanner);
    }

    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    public function setFeatured($featured)
    {
        return $this->setData(self::FEATURED, $featured);
    }

    public function setShortDescription($shortDescription)
    {
        return $this->setData(self::SHORT_DESCRIPTION, $shortDescription);
    }

    public function setMainDescription($mainDescription)
    {
        return $this->setData(self::MAIN_DESCRIPTION, $mainDescription);
    }

    public function setMetaKeywords($metaKeywords)
    {
        return $this->setData(self::META_KEYWORDS, $metaKeywords);
    }

    public function setMetaDescription($metaDescription)
    {
        return $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    public function isEnabled()
    {
        return $this->isStatus(self::STATUS_ENABLED);
    }

    public function isDisabled()
    {
        return $this->isStatus(self::STATUS_DISABLED);
    }

    public function isStatus($status)
    {
        return $this->getStatus() === $status;
    }

    /**
     * @param $identifier
     * @return mixed
     * @throws LocalizedException
     */
    public function checkIdentifier($identifier)
    {
        return $this->_getResource()->checkIdentifier($identifier);
    }

    /**
     * Get logo url.
     *
     * @return string
     */
    public function getImageLogoUrl()
    {
        $baseUrl = $this->_urlBuilder->getBaseUrl([
            '_type' => UrlInterface::URL_TYPE_MEDIA
        ]);

        return $baseUrl . 'logo/logo/' . $this->getLogo();
    }

    /**
     * Get logo name.
     *
     * @return string
     */
    public function getImageLogoName()
    {
        return $this->getLogo();
    }

    /**
     * Get brand banner url.
     *
     * @return string
     */
    public function getImageBrandBannerUrl()
    {
        $baseUrl = $this->_urlBuilder->getBaseUrl([
            '_type' => UrlInterface::URL_TYPE_MEDIA
        ]);

        return $baseUrl . 'brandpage/brandpage/' . $this->getBrandBanner();
}

    /**
     * Get product banner url.
     *
     * @return string
     */
    public function getImageProductBannerUrl()
    {
        $baseUrl = $this->_urlBuilder->getBaseUrl([
            '_type' => UrlInterface::URL_TYPE_MEDIA
        ]);

//        return $baseUrl . 'brandproductpage/brandproductpage/' . $this->getProductBanner();
        return $this->_helper->resizeImageUrl($this->getProductBanner(), $this->_helper->getProductPageLogoWidth(), null, 'product-logo');
    }
}