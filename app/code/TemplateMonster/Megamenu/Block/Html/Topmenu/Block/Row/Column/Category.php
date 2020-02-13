<?php

namespace TemplateMonster\Megamenu\Block\Html\Topmenu\Block\Row\Column;

class Category extends Entity
{
    const MAX_CATS = 100000;

    protected $_template = 'html/topmenu/block/row/column/category.phtml';

    public function getCategory()
    {
        return $this->getEntity()->getCategory();
    }

    public function getImage($node)
    {
        $url = "";
        if ($image = $node->getMmImage()) {
            if ($image) {
                $url = $this->_storeManager->getStore()->getBaseUrl(
                        \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                    ) . 'catalog/category/' . $image;
            }
        }
        return $url;
    }

    public function getShowSubcategories($category)
    {
        $mmShowSubCategories = $category->getMmShowSubcategories();
        if (is_null($mmShowSubCategories)) {
            return true;
        }
        return $mmShowSubCategories;
    }

    public function getNumberOfSubcategories($category)
    {
        $mmNumber = $category->getMmNumberOfSubcategories();
        if (is_null($mmNumber)) {
            return self::MAX_CATS;
        }
        if (!is_numeric($mmNumber)) {
            return self::MAX_CATS;
        }
        return $mmNumber;
    }

    public function getCssClass($category)
    {
        $class = '';
        if ($mmCssClass = $category->getMmCssClass()) {
            $class .= $mmCssClass;
        }
        if ($category->getIsActive()) {
            $class .= " active";
        } elseif ($category->getHasActive()) {
            $class .= " has-active";
        }

        return $class ? ' ' . $class : '';
    }

}