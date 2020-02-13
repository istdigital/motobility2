<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\CatalogImagesGrid\Plugin\Catalog\Model\Category;

class DataProvider
{
    protected $_storeManager;

    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->_storeManager = $storeManager;
    }

    public function afterGetData(\Magento\Catalog\Model\Category\DataProvider $subject,$result)
    {
        $categoryId = $subject->getCurrentCategory()->getId();
        if ($categoryId && isset($result[$categoryId]['thumbnail'])) {
            unset($result[$categoryId]['thumbnail']);
            $category = $subject->getCurrentCategory();
            $result[$categoryId]['thumbnail'][0]['name'] = $category->getData('thumbnail');
            $result[$categoryId]['thumbnail'][0]['url'] = $this->getImageUrl($category->getData('thumbnail'));
        }
        return $result;
    }

    /**
     * @param $image
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getImageUrl($image)
    {
        $url = false;
        if ($image) {
            if (is_string($image)) {
                $url = $this->_storeManager->getStore()->getBaseUrl(
                        \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                    ) . 'catalog/category/' . $image;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        }
        return $url;
    }
}