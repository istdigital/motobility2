<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FeaturedProduct\Block\FeaturedProduct\Widget;

use Magento\Customer\Model\Context as CustomerContext;
use \Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Url\EncoderInterface;

class Product extends \Magento\Catalog\Block\Product\AbstractProduct implements \Magento\Widget\Block\BlockInterface
{

    /**
     * Default cache time
     */
    const CACHE_TIME = 3600;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Widget\Helper\Conditions
     */
    protected $conditionsHelper;


    /**
     * @var \Magento\Framework\Registry $coreRegistry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\ProductVideo\Helper\Media
     */
    protected $mediaHelper;

    /**
     * Category collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    protected $_template = 'TemplateMonster_FeaturedProduct::widget/products-grid.phtml';
    protected $_productFactory;
    protected $_imageBuilder;
    protected $_httpContext;

    protected $_isCategoriesTab;


    public function __construct(
        FilterProvider $filterProvider,
        \Magento\Directory\Model\PriceCurrency $priceCurrency,
        \Magento\Widget\Helper\Conditions $conditionsHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\ProductVideo\Helper\Media $mediaHelper,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Block\Product\Context $context,
        \TemplateMonster\FeaturedProduct\Model\ProductFactory $productFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = [],
        EncoderInterface $urlEncoder = null)
    {
        $this->_filterProvider = $filterProvider;
        $this->priceCurrency = $priceCurrency;
        $this->conditionsHelper = $conditionsHelper;
        $this->jsonEncoder = $jsonEncoder;
        $this->mediaHelper = $mediaHelper;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_imageBuilder = $context->getImageBuilder();
        $this->_productFactory = $productFactory;
        $this->_httpContext = $httpContext;
        $this->_isCategoriesTab = false;
        $this->_coreRegistry = $context->getRegistry();
        $this->urlEncoder = $urlEncoder ?: \Magento\Framework\App\ObjectManager::getInstance()->get(EncoderInterface::class);
        parent::__construct($context, $data);
    }


    public function _construct()
    {
        $this->addData(
            ['cache_lifetime' => self::CACHE_TIME, 'cache_tags' => [\Magento\Catalog\Model\Product::CACHE_TAG]]
        );
        parent::_construct();
    }

    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheKeyFromData = "TM_";
        $configData = $this->getData();

        foreach($configData as $value) {
            if(is_string($value)) {
                $cacheKeyFromData .= $value;

            }
        }
        return [
            $cacheKeyFromData,
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->_storeManager->getStore()->getCurrentCurrency()->getCode(),
            $this->_httpContext->getValue(CustomerContext::CONTEXT_GROUP),
            'template' => $this->getTemplate()
        ];
    }

    public function getCacheLifetime()
    {
        if(is_null($this->getData('cache_lifetime'))) {
            return self::CACHE_TIME;
        }
        return $this->getData('cache_lifetime');
    }

    public function getProductIds()
    {
        $productArr = [];
        //TODO: temporary hide manual products
        $productIdsStr = $this->getData('product_ids');
//        $productIdsStr = false;
        if($productIdsStr && is_string($productIdsStr)) {
            $productArr = explode(',',$productIdsStr);
        }
        return $productArr;
    }

    public function getItemWidth()
    {
        $productItemWidth = '';
        $productPerRow = $this->getNumberPerRow();
        if ($productPerRow) {
            if (!$this->getShowCarousel()) {
                $productItemWidth = 'style="width: ' . 100 / $productPerRow . '%; max-width: ' . 100 / $productPerRow . '%;"';
            }
        }
        return $productItemWidth;
    }

    /**
     * Get array of available titles
     *
     * @return array
     */

    public function getTextData()
    {
        return json_decode(str_replace(['\'', '`'], ['"', '"'], $this->getJsonData()), true);
    }

    /**
     * Get array of product types
     *
     * @return array
     */

    public function getTypes()
    {
        return explode(",", $this->getProductTypes());
    }

    /**
     * Get array of category ids
     *
     * @return array
     */

    public function getCategoryIds()
    {
        $conditions = $this->conditionsHelper->decode($this->getConditionsEncoded());
        $ids = [];
        if($conditions){
            $ids = array_key_exists('1--1', $conditions) ? explode(', ', $conditions['1--1']['value']) : [];
        }
        return empty($ids[0]) ? [] : $ids;
    }

    /**
     * Truncate product name
     *
     * @param string $name
     * @return string
     */
    public function truncateProductName($name)
    {
        $name = $this->escapeHtml($name);
        $truncate = (int) $this->getProductNameLength();
        $nameLength = strlen($name);
        if($truncate != 0 && is_int($truncate) && $nameLength > $truncate){
            $name = substr($name, 0, $truncate) . '...';
        }
        return $name;
    }

    public function getCategoryNames()
    {

        $categories = $this->getCategoryIds();
        if(empty($categories)) {
            return [];
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Category $collection */
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addIdFilter($categories);

        $collection->addNameToResult();

        $names = [];
        foreach ($collection as $category) {
            /** @var \Magento\Catalog\Model\Category $category */
            $names[$category->getId()] = $category->getName();
        }

        return $names;
    }

    /**
     * Return Product Collection
     * @return array
     */
    protected function _getProductCollections()
    {
        $productIds = $this->getProductIds();
        $numberPerView = (int)$this->getNumberPerView();
        $productCollectionsArray = false;


        $tabsContent = $this->_getTabsType();
        $tabs = ($tabsContent['type'] == 'product_types') ? $tabsContent['primary'] : $tabsContent['secondary'];
        $categoryIds = ($tabsContent['type'] == 'product_types') ? $tabsContent['secondary'] : $tabsContent['primary'];

        foreach ($tabs as $products){
            try {
                $productFactory = $this->_productFactory->create($products);
            } catch(\Exception $e) {
                continue;
            }
            $manualIds = ($products == 'manual_product') ? $productIds : [];
            if (empty($categoryIds)){
                $productCollectionsArray[$products]['all'] = $productFactory->getPreparedCollection($numberPerView, $manualIds);
            }
            foreach ($categoryIds as $categoryId){
                if($tabsContent['type'] == 'product_types') {
                    if(count($tabsContent['primary']) > 1) {
                        $productCollectionsArray[$products]['all'] = $productFactory->getPreparedCollection($numberPerView, $manualIds, $categoryIds);
                        break;
                    } else {
                        $productCollectionsArray[$products][$categoryId] = $productFactory->getPreparedCollection($numberPerView, $manualIds, $categoryId);
                    }
                } else {
                    $productCollectionsArray[$categoryId][$products] = $productFactory->getPreparedCollection($numberPerView, $manualIds, $categoryId);
                }
            }
        }

        return $productCollectionsArray;
    }

    /**
     *
     */
    protected function _getTabsType()
    {
        $productTypesCount = count($this->getTypes());
        $categoryIdsCount = count($this->getCategoryIds());
        if($productTypesCount > 1 && $categoryIdsCount == 1) {
            $tabs = [
                'type'      => 'categories',
                'primary'   => $this->getCategoryIds(),
                'secondary' => $this->getTypes(),
            ];
        } else {
            $tabs = [
                'type'      => 'product_types',
                'primary'   => $this->getTypes(),
                'secondary' => $this->getCategoryIds(),
            ];
        }
        return $tabs;
    }


    public function isCategoryPrimaryTabs()
    {
        $tabs = $this->_getTabsType();
        return $tabs['type'] == 'categories';
    }

    public function isProductTypePrimaryTabs()
    {
        $tabs = $this->_getTabsType();
        return $tabs['type'] == 'product_types';
    }

    /**
     * Return HTML block with price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType = null,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['zone'] = isset($arguments['zone'])
            ? $arguments['zone']
            : $renderZone;
        $arguments['price_id'] = isset($arguments['price_id'])
            ? $arguments['price_id']
            : 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container'] = isset($arguments['include_container'])
            ? $arguments['include_container']
            : true;
        $arguments['display_minimal_price'] = isset($arguments['display_minimal_price'])
            ? $arguments['display_minimal_price']
            : true;

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                $arguments
            );
        }
        return $price;
    }

    public function getProductsLabel($product, $label)
    {
        return $this->replaceString($product, $label);
    }

    public function replaceString($product, $string)
    {
        switch ($string) {

            case $this->findSubstring($string, "{SAVE_PERCENT}"):
                return $this->replaceSubstring("{SAVE_PERCENT}", $this->getSavePercent($product), $string);

            case $this->findSubstring($string, "{SAVE_AMOUNT}"):
                return $this->replaceSubstring("{SAVE_AMOUNT}", $this->getSaveAmount($product), $string);

            case $this->findSubstring($string, "{PRICE}"):
                return $this->replaceSubstring("{PRICE}", $this->getFormatedPrice($product), $string);

            case $this->findSubstring($string, "{SPECIAL_PRICE}"):
                return $this->replaceSubstring("{SPECIAL_PRICE}", $this->getSpecialPrice($product), $string);

            case $this->findSubstring($string, "{NEW_FOR}"):
                return $this->replaceSubstring("{NEW_FOR}", $this->getNewFor($product), $string);

            case $this->findSubstring($string, "{SKU}"):
                return $this->replaceSubstring("{SKU}", $this->getSku($product), $string);

            case $this->findSubstring($string, '{ATTR:'):
                return $this->attrCode($product, $string);

            case $this->findSubstring($string, "{BR}"):
                return $this->breakString($string);

            default:
                return $string;

        }
    }

    protected function findSubstring($string, $substring)
    {
        return (strpos($string, $substring) !== false);
    }

    protected function replaceSubstring($substring, $replacement, $string) {
        return str_replace($substring, $replacement, $string);
    }

    public function getSavePercent($product)
    {
        if ($product->getPrice() && $product->getFinalPrice()) {
            $savePercent =  ceil(
                100 - ((100 / $product->getPrice())
                    * $product->getFinalPrice())
            );
        } else {
            $savePercent = 0;
        }

        return $savePercent.'%';
    }

    public function getSaveAmount($product)
    {
        if ($product->getPrice() <= $product->getFinalPrice()) {
            return false;
        }
        $price = $product->getPrice() - $product->getFinalPrice();
        $priceInCurrentCurrency = $this->priceCurrency->convertAndRound($price);
        return $this->priceCurrency->format($priceInCurrentCurrency);
    }

    public function getFormatedPrice($product)
    {
        return $product->getFormatedPrice();
    }

    public function getSpecialPrice($product)
    {
        $specialPrice = $product->getSpecialPrice();
        return $this->priceCurrency->format($specialPrice);
    }

    public function getNewFor($product)
    {
        $created = $product->getCreatedAt();

        $dStart = new \DateTime();
        $dEnd  = new \DateTime($created);
        $dDiff = $dStart->diff($dEnd);

        if ($dDiff->days <= 0) {
            return false;
        }

        return $dDiff->days;
    }

    public function getSku($product)
    {
        return $product->getData('sku');
    }

    public function attrCode($product, $string)
    {
        preg_match("/{([^}]+)}*/i", $string, $placeholder);
        preg_match_all('/\{.*?}/', $placeholder[0], $placeholderText);
        $attr = str_replace(['{', '}', ':', 'ATTR'], '', $placeholderText[0][0]);
        $result = str_replace($placeholder[0], $product->getData($attr), $string);
        if (!$attr || !$result) {
            return false;
        }
        return $result;
    }

    public function breakString($string)
    {
        return str_replace('{BR}', '<br/>', $string);
    }

    public function filterContent($data)
    {
        return $this->_filterProvider->getBlockFilter()->filter($data);
    }

    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        $hoverType = $this->getHoverType();
        $imageSize = [];
        if($this->getProductImageWidth() || $this->getProductImageWidth()) {
            $imageSize = [
                'width' => $this->getProductImageWidth(),
                'height' => $this->getProductImageHeight(),
            ];
        }

        $this->_coreRegistry->register('featured_product', $imageSize);

        $result = parent::getImage($product, $imageId, $attributes);
        $this->_coreRegistry->unregister('featured_product');
        $image = $product->getCustomAttribute('small_image');
        $imageInit = $this->_imageHelper->init($product, $imageId, $imageSize)->setImageFile($image->getValue())->getUrl();
        $result->setImageUrl($imageInit);
        $result->setTemplate('TemplateMonster_FeaturedProduct::product/image_with_borders.phtml');

        if(!$hoverType) {
            return $result;
        }
        switch ($hoverType) {
            case ('switcher'):
                /**
                 * @var $productImg \Magento\Catalog\Block\Product\Image
                 */
                $result->setTemplate('TemplateMonster_FeaturedProduct::product/switcher.phtml');
                $onHover = $product->getCustomAttribute('on_hover');
                $video = $this->_getVideoUrl($product);
                $attributeImageUrl = false;
                if($onHover && $onHover->getValue() != 'no_selection') {
                    $attributeImageUrl = $video
                        ? false
                        : $this->_imageHelper->init($product, $imageId, $imageSize)->setImageFile($onHover->getValue())->getUrl();
                }
                $result->setVideoUrl($video);
                $result->setReplaceImageUrl($attributeImageUrl);
                break;
            case ('carousel'):
                /** @var $result \TemplateMonster\FeaturedProduct\Block\Product\View\Gallery */
                $result = $this->_layout->createBlock('TemplateMonster\FeaturedProduct\Block\Product\View\Gallery')
                    ->setTemplate('TemplateMonster_FeaturedProduct::product/carousel.phtml');
                $this->_setupGallery($product, $result);
                $result->setSlidesCount($this->getCarouselSlidesCount())
                       ->setThumbnailSize([])
                       ->setSlideSize($imageSize);
                break;
            case ('gallery'):
                /** @var $result \TemplateMonster\FeaturedProduct\Block\Product\View\Gallery */
                $result = $this->_layout->createBlock('TemplateMonster\FeaturedProduct\Block\Product\View\Gallery')
                    ->setTemplate('TemplateMonster_FeaturedProduct::product/gallery.phtml');
                $this->_setupGallery($product, $result);
                $result->setSlidesCount($this->getGallerySlidesCount())
                       ->setThumbnailSize(['width' => $this->getThumbWidth(), 'height' => $this->getThumbHeight()])
                       ->setSlideSize($imageSize);
                break;
            default: break;
        }

        return $result;

    }

    protected function _setupGallery($product, $result)
    {
        $product->load($product->getId());
        $result->setProduct($product);
        $result->setVideoData($this->getGalleryDataJson($product));
        $result->setVideoSettings($this->getVideoSettings());
    }


    /**
     * Retrieve media gallery data in JSON format
     *
     * @return string
     */
    public function getGalleryDataJson($product)
    {
        $mediaGalleryData = [];
        foreach ($product->getMediaGalleryImages() as $mediaGalleryImage) {
            $mediaGalleryData[] = [
                'mediaType' => $mediaGalleryImage->getMediaType(),
                'videoUrl' => $mediaGalleryImage->getVideoUrl(),
                'isBase' => $this->isMainImage($product, $mediaGalleryImage),
            ];
        }
        return $this->jsonEncoder->encode($mediaGalleryData);
    }

    /**
     * Retrieve video settings data in JSON format
     *
     * @return string
     */
    public function getVideoSettings()
    {
        $videoSettingData[] = [
            'playIfBase' => $this->mediaHelper->getPlayIfBaseAttribute(),
            'showRelated' => $this->mediaHelper->getShowRelatedAttribute(),
            'videoAutoRestart' => $this->mediaHelper->getVideoAutoRestartAttribute(),
        ];
        return $this->jsonEncoder->encode($videoSettingData);
    }

    /**
     * Is product main image
     *
     * @param \Magento\Framework\DataObject $image
     * @return bool
     */
    public function isMainImage($product, $image)
    {
        return $product->getImage() == $image->getFile();
    }

    protected function _getVideoUrl($product)
    {
        $video = false;
        foreach ($product->getMediaGalleryImages()->getItems() as $image) {
            if($image->getMediaType() == 'external-video') {
                $videoUrl = $image->getVideoUrl();
                $search     = '/youtube\.com\/watch\?v=([a-zA-Z0-9]+)/smi';
                $replace    = "youtube.com/embed/$1";

                $onHoverValue = $product->getCustomAttribute('on_hover');
                $video = ($onHoverValue && $image->getFile() == $onHoverValue->getValue())
                    ? preg_replace($search,$replace,$videoUrl)
                    : false;
            }


        }
        return $video;
    }

    /**
     * Prepare collection with new products
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _beforeToHtml()
    {
        $this->setProductCollections($this->_getProductCollections());
        return parent::_beforeToHtml();
    }

    /**
     * Get post parameters.
     *
     * @param Product $product
     * @return array
     */
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlEncoder->encode($url),
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAddToCartUrl($product, $additional = [])
    {
        $requestingPageUrl = $this->getRequest()->getParam('requesting_page_url');

        if (!empty($requestingPageUrl)) {
            $additional['useUencPlaceholder'] = true;
            $url = parent::getAddToCartUrl($product, $additional);
            return str_replace('%25uenc%25', $this->urlEncoder->encode($requestingPageUrl), $url);
        }

        return parent::getAddToCartUrl($product, $additional);
    }


    /**
     * Render block HTML
     *
     * @return string
     */
    public function toHtml()
    {
        return $this->getWidgetStatus() === '0' ? '' : parent::toHtml();
    }
}