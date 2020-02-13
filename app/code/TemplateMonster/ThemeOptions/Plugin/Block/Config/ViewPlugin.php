<?php
/**
 * Copyright © 2016 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace TemplateMonster\ThemeOptions\Plugin\Block\Config;

use \TemplateMonster\ThemeOptions\Helper\Data;
use \Magento\Framework\Config\View;


class ViewPlugin
{
    /**
     * Theme options helper
     */
    protected $_helper;

    /**
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->_helper = $helper;
    }

    protected function getDimensions() {
        $dimensions = [
            'category_page_grid' => [
                'width' => $this->_helper->getCategoryThumbWidth('grid'),
                'height' => $this->_helper->getCategoryThumbHeight('grid'),
                'aspect_ratio' => $this->_helper->getCategoryThumbRatio('grid'),
            ],
            'category_page_list' => [
                'width' => $this->_helper->getCategoryThumbWidth('list'),
                'height' => $this->_helper->getCategoryThumbHeight('list'),
                'aspect_ratio' => $this->_helper->getCategoryThumbRatio('list'),
            ],
            'grid_gallery_thumb' => [
                'width' => $this->_helper->getHoverTypeThumbWidth(),
                'height' => $this->_helper->getHoverTypeThumbHeight(),
            ],
            'upsell_products_list' => [
                'width' => $this->_helper->getProductDetailUpsellImageWidth(),
                'height' => $this->_helper->getProductDetailUpsellImageHeight(),
            ],
            'related_products_list' => [
                'width' => $this->_helper->getProductDetailRelatedImageWidth(),
                'height' => $this->_helper->getProductDetailRelatedImageHeight(),
            ],
            'product_page_image_medium' => [
                'width' => $this->_helper->getProductGalleryImgWidth(),
                'height' => $this->_helper->getProductGalleryImgHeight(),
            ],
            'product_page_image_medium_no_frame' => [
                'width' => $this->_helper->getProductGalleryImgWidth(),
                'height' => $this->_helper->getProductGalleryImgHeight(),
            ]
        ];

        return $dimensions;
    }

    /**
     * Load configuration scope
     *
     * @param string|null $scope
     * @return array
     */
    public function afterRead(View $subject, $result, $scope = null)
    {
        foreach ($this->getDimensions() as $mediaId => $dimension){

            if(!empty($dimension['width']) && !is_null($dimension['width'])) {
                $result['media']['Magento_Catalog']['images'][$mediaId]['width'] = $dimension['width'];
            }
            if(!empty($dimension['height']) && !is_null($dimension['height'])) {
                $result['media']['Magento_Catalog']['images'][$mediaId]['height'] = $dimension['height'];
            }
            if(!empty($dimension['aspect_ratio']) && !is_null($dimension['aspect_ratio'])) {
                $result['media']['Magento_Catalog']['images'][$mediaId]['aspect_ratio'] = $dimension['aspect_ratio'];
            }
        }

        return $result;
    }


}
