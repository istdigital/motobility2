<?php
namespace TemplateMonster\CatalogImagesGrid\Helper;

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

use Magento\Store\Model\ScopeInterface;

class CategoryImage  extends \Magento\Framework\App\Helper\AbstractHelper
{

    const ENABLE_MODULE = 'catalogimagesgrid/general/enable';

    const ENABLE_ON_CATEGORY_PAGE = 'catalogimagesgrid/category_page/enable';

    const IMAGE_TYPE = 'catalogimagesgrid/category_page/use_image';

    const ENABLE_IMAGE_CATEGORY = 'catalogimagesgrid/category_page/enable_image_categories';

    const ENABLE_IMAGE_SUBCATEGORY = 'catalogimagesgrid/category_page/enable_image_subcategories';

    const COLUMNS_COUNT = 'catalogimagesgrid/category_page/columns_count';

    const IMAGE_WIDTH = 'catalogimagesgrid/category_page/image_width';

    const ICON_SIZE = 'catalogimagesgrid/category_page/icon_size';

    const AMOUNT_CATEGORY = 'catalogimagesgrid/category_page/amount_categories';

    const AMOUNT_SUBCATEGORY = 'catalogimagesgrid/category_page/amount_subcategories';

    const VIEW_MORE = 'catalogimagesgrid/category_page/view_more';


    protected $_registry;

    protected $_storeManager;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_registry = $registry;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function getCategory()
    {
        return $this->_registry->registry('current_category');
    }

    public function getSubCategories($category,$amountSubcategories)
    {
        if($category && $category->getAllChildren(true) && $amountSubcategories) {
            $ids = $category->getAllChildren(true);
            $idsFiltered = array_slice($ids,0,$amountSubcategories);
            $categoryCollection = $category->getCollection();
            $categoryCollection->addAttributeToFilter('entity_id',['in'=>$idsFiltered]);
            $categoryCollection->addAttributeToSelect(['thumbnail','image']);
            return $categoryCollection;
        }

        return null;
    }

    public function getImageByType($category, $type = false)
    {
        $type = ($type) ? $type : $this->getImageType();
        if($type == 'thumbnail_image') {
            $image = $category->getThumbnail();
        } elseif($type == 'category_image') {
            $image = $category->getImage();
        } else {
            return false;
        }

        $url = false;
        if ($image) {
            if (is_string($image)) {
                $url = $this->_storeManager->getStore()->getBaseUrl(
                        \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                    ) . 'catalog/category/' . $image;
            } else {
                $url = false;
            }
        }
        return $url;
    }

    public function getChildrens($category)
    {
        $collection = $category->getCollection();
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        $collection->addAttributeToSelect(
            'url_key'
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'all_children'
        )->addAttributeToSelect(
            'thumbnail'
        )->addAttributeToSelect(
            'image'
        )->addAttributeToSelect(
            'icon_class'
        )->addAttributeToSelect(
            'is_anchor'
        )->addAttributeToFilter(
            'is_active',
            1
        )->addIdFilter(
            $category->getChildren()
        )->setOrder(
            'position',
            \Magento\Framework\DB\Select::SQL_ASC
        )->load();

        if(is_a($collection,'Magento\Catalog\Model\ResourceModel\Category\Flat\Collection')){
            $collection->addUrlRewriteToResult();
        } else {
            $collection->joinUrlRewrite();
        }
        return $collection;
    }


    /**
     * @return bool
     */
    public function isEnableModule()
    {
        return $this->scopeConfig->isSetFlag(
            self::ENABLE_MODULE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isEnableOnCategoryPage()
    {
        return $this->scopeConfig->isSetFlag(
            self::ENABLE_ON_CATEGORY_PAGE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getEnableImageSubcategory()
    {
        return $this->scopeConfig->getValue(
            self::ENABLE_IMAGE_SUBCATEGORY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getEnableImageCategory()
    {
        return $this->scopeConfig->getValue(
            self::ENABLE_IMAGE_CATEGORY,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getImageType()
    {
        return $this->scopeConfig->getValue(
            self::IMAGE_TYPE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getAmountOfSubcategory()
    {
        return $this->scopeConfig->getValue(
            self::AMOUNT_SUBCATEGORY,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isViewMore()
    {
        return $this->scopeConfig->getValue(
            self::VIEW_MORE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getAmountOfCategory()
    {
        return $this->scopeConfig->getValue(
            self::AMOUNT_CATEGORY,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getImageWidth()
    {
        return $this->scopeConfig->getValue(
            self::IMAGE_WIDTH,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getIconSize()
    {
        return $this->scopeConfig->getValue(
            self::ICON_SIZE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getColumnsCount() {
        return $this->scopeConfig->getValue(
            self::COLUMNS_COUNT,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getColumnsWidth($count = NULL)
    {
        $columns = 4;
        if ($count != NULL) {
            $columns = $count;
        }
        $columnsWidth = round(100 / $columns, 3);
        $columnsWidth .= '%';
        return $columnsWidth;

    }

}