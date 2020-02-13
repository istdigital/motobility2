<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Block\Category;

use Magento\Framework\App\ObjectManager;
use Mageplaza\Blog\Block\Adminhtml\Category\Tree;
use Mageplaza\Blog\Block\Frontend;
use Mageplaza\Blog\Helper\Data;

/**
 * Class Widget
 * @package Mageplaza\Blog\Block\Category
 */
class Widget extends Frontend
{
    /**
     * @return array|string
     */
    public function getTree()
    {
        $tree = ObjectManager::getInstance()->create(Tree::class);
        $tree = $tree->getTree(null, $this->store->getStore()->getId());

        return $tree;
    }

    /**
     * @param $tree
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getCategoryTreeHtml($tree)
    {
        if (!$tree) {
            return __('No Categories.');
        }

        $html = '';
        foreach ($tree as $value) {
            if (!$value) {
                continue;
            }
            if ($value['enabled']) {
                $id = $this->getRequest()->getParam('id');
                $activeclass = '';
                if($id == $value['id']){
                    $activeclass = 'active';
                }
                $level = count(explode('/', ($value['path'])));
                $hasChild = isset($value['children']) && $level < 4;
                $html .= '<ul class="block-content menu-categories category-level' . $level . '">';
                $html .= '<li class="category-item">';
                $html .= '<a class="list-categories '.$activeclass.'" href="' . $this->getCategoryUrl($value['url']) . '">';
                $html .= ucfirst($value['text']);
                $html .= '</a>';
                $html .= $hasChild ? $this->getCategoryTreeHtml($value['children']) : '';
                $html .= '</li>';
                $html .= '</ul>';
            }
        }

        return $html;
    }

    /**
     * @param $category
     *
     * @return string
     */
    public function getCategoryUrl($category)
    {
        return $this->helperData->getBlogUrl($category, Data::TYPE_CATEGORY);
    }
    public function getSidebarCategoryCollection()
    {
        return $this->helperData->getSidebarCategoryCollection();
    }
    public function isActive($id)
    {
        $_id = $this->getRequest()->getParam('id');
        if($id == $_id){
            return 'active';
        }
        return '';
    }
    
    
}
