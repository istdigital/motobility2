<?php

namespace TemplateMonster\CatalogImagesGrid\Block\CatalogImage;

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
class Grid  extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{

    /**
     * Default cache time
     */
    const CACHE_TIME = 86400;

    protected $_categoryRepository;

    protected $_helperCategoryImage;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context,
                                \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
                                \TemplateMonster\CatalogImagesGrid\Helper\CategoryImage $helperCategoryImage,
                                array $data)
    {
        $this->_categoryRepository = $categoryRepository;
        $this->_helperCategoryImage = $helperCategoryImage;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        $this->addData(
            ['cache_lifetime' => self::CACHE_TIME, 'cache_tags' => [\Magento\Catalog\Model\Category::CACHE_TAG]]
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
            'template' => $this->getTemplate()
        ];
    }

    public function getCacheLifetime()
    {
        if(!$this->getData('cache_lifetime')) {
            return self::CACHE_TIME;
        }
        return $this->getData('cache_lifetime');
    }

    public function getCategory()
    {
        $categoryIdPath = $this->getIdPath();
        if($categoryId=str_replace('category/','',$categoryIdPath))
        {
            return $this->_categoryRepository->get($categoryId);
        }
        return null;
    }

    public function getSubCategories()
    {
        $category = $this->getCategory();
        $amountSubcategories = $this->getAmountSubcategories();
        return $this->_helperCategoryImage->getSubCategories($category,$amountSubcategories);
    }

    public function getImageByType($category, $imageType = false)
    {
        return $this->_helperCategoryImage->getImageByType($category, $imageType);
    }

    public function getChildrens($category)
    {
        return $this->_helperCategoryImage->getChildrens($category);
    }

    public function getColumnsWidth($count)
    {
        return $this->_helperCategoryImage->getColumnsWidth($count);
    }

    public function getTitle()
    {
      return $this->getData('title');
    }

    public function getViewMoreText()
    {
        return $this->getData('view_more_text');
    }

    protected function _toHtml()
    {
        return ($this->_helperCategoryImage->isEnableModule() && $this->getWidgetStatus()) ? parent::_toHtml() : '';
    }
}