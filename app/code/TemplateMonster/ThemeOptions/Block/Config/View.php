<?php
/**
 * Copyright © 2016 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace TemplateMonster\ThemeOptions\Block\Config;

use \TemplateMonster\ThemeOptions\Helper\Data;
use \Magento\Framework\Config\FileResolverInterface;
use \Magento\Framework\Config\ConverterInterface;
use \Magento\Framework\Config\SchemaLocatorInterface;
use \Magento\Framework\Config\ValidationStateInterface;

class View extends \Magento\Framework\Config\View
{
    /**
     * Theme options helper
     */
    protected $_helper;

    public function __construct(
        Data $helper,
        FileResolverInterface $fileResolver,
        ConverterInterface $converter,
        SchemaLocatorInterface $schemaLocator,
        ValidationStateInterface $validationState,
        $fileName,
        $idAttributes = [],
        $domDocumentClass = 'Magento\Framework\Config\Dom',
        $defaultScope = 'global',
        $xpath = []
    ) {
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName,
            $idAttributes,
            $domDocumentClass,
            $defaultScope,
            $xpath
        );
        $this->_helper = $helper;
    }

    /**
     * Retrieve array of media attributes
     */
    public function getMediaAttributes($module, $mediaType, $mediaId)
    {
        $this->initData();
        $mediaData = isset($this->data['media'][$module][$mediaType][$mediaId])
            ? $this->data['media'][$module][$mediaType][$mediaId]
            : [];
        $imageDimensions = $this->getImagesDimensions($module, $mediaType, $mediaId);
        if ($imageDimensions && $mediaData) {
            return $this->substituteValue($mediaData, $imageDimensions);
        }
        return parent::getMediaAttributes($module, $mediaType, $mediaId);
    }

    /**
     * Retrieve image dimensions from Theme Options.
     *
     */
    protected function getImagesDimensions($module, $mediaType, $mediaId)
    {
        $dimensions = [];
        if ($module == 'Magento_Catalog' && $mediaType == 'images') {
            if ($mediaId == 'category_page_grid') {
                if (!empty($this->_helper->getCategoryThumbWidth('grid'))) {
                    $dimensions['grid_image_width'] = $this->_helper->getCategoryThumbWidth('grid');
                }
                if (!empty($this->_helper->getCategoryThumbHeight('grid'))) {
                    $dimensions['grid_image_height'] = $this->_helper->getCategoryThumbHeight('grid');
                }
                if (!empty($this->_helper->getCategoryThumbRatio('grid'))) {
                    $dimensions['grid_image_ratio'] = $this->_helper->getCategoryThumbRatio('grid');
                }
            }

           if ($mediaId == 'product_page_image_small') {
               if (!empty($this->_helper->getHoverTypeThumbWidth())) {
                   $dimensions['grid_hover_thumb_width'] = $this->_helper->getHoverTypeThumbWidth();
               }
               if (!empty($this->_helper->getHoverTypeThumbHeight())) {
                   $dimensions['grid_hover_thumb_height'] = $this->_helper->getHoverTypeThumbHeight();
               }
           }

            if ($mediaId == 'category_page_list') {
                if (!empty($this->_helper->getCategoryThumbWidth('list'))) {
                    $dimensions['list_image_width'] = $this->_helper->getCategoryThumbWidth('list');
                }
                if (!empty($this->_helper->getCategoryThumbWidth('list'))) {
                    $dimensions['list_image_height'] = $this->_helper->getCategoryThumbWidth('list');
                }
                if (!empty($this->_helper->getCategoryThumbRatio('list'))) {
                    $dimensions['grid_image_ratio'] = $this->_helper->getCategoryThumbRatio('list');
                }
            }
            if ($mediaId == 'upsell_products_list') {
                if (!empty($this->_helper->getProductDetailUpsellImageWidth())) {
                    $dimensions['upsell_image_width'] = $this->_helper->getProductDetailUpsellImageWidth();
                }
                if (!empty($this->_helper->getProductDetailUpsellImageHeight())) {
                    $dimensions['upsell_image_height'] = $this->_helper->getProductDetailUpsellImageHeight();
                }
            }
            if ($mediaId == 'related_products_list') {
                if (!empty($this->_helper->getProductDetailRelatedImageWidth())) {
                    $dimensions['related_image_width'] = $this->_helper->getProductDetailRelatedImageWidth();
                }
                if (!empty($this->_helper->getProductDetailRelatedImageHeight())) {
                    $dimensions['related_image_height'] = $this->_helper->getProductDetailRelatedImageHeight();
                }
            }
            if ($mediaId == 'product_page_image_medium') {
                if (!empty($this->_helper->getProductGalleryImgWidth())) {
                    $dimensions['page_image_width'] = $this->_helper->getProductGalleryImgWidth();
                }
                if (!empty($this->_helper->getProductGalleryImgHeight())) {
                    $dimensions['page_image_height'] = $this->_helper->getProductGalleryImgHeight();
                }
            }
            if ($mediaId == 'product_page_image_medium_no_frame') {
                if (!empty($this->_helper->getProductGalleryImgWidth())) {
                    $dimensions['page_image_width'] = $this->_helper->getProductGalleryImgWidth();
                }
                if (!empty($this->_helper->getProductGalleryImgHeight())) {
                    $dimensions['page_image_height'] = $this->_helper->getProductGalleryImgHeight();
                }
            }
        }
        return $dimensions;
    }


    protected function substituteValue($mediaData, $value)
    {
        if (isset($value['grid_image_width'])) {
            $mediaData['width'] = $value['grid_image_width'];
        }
        if (isset($value['grid_image_height'])) {
            $mediaData['height'] = $value['grid_image_height'];
        }
        if (isset($value['grid_image_ratio'])) {
            $mediaData['aspect_ratio'] = $value['grid_image_ratio'];
        }
        if (isset($value['list_image_width'])) {
            $mediaData['width'] = $value['list_image_width'];
        }
        if (isset($value['list_image_height'])) {
            $mediaData['height'] = $value['list_image_height'];
        }
        if (isset($value['list_image_ratio'])) {
            $mediaData['aspect_ratio'] = $value['list_image_ratio'];
        }
        if (isset($value['upsell_image_width'])) {
            $mediaData['width'] = $value['upsell_image_width'];
        }
        if (isset($value['upsell_image_height'])) {
            $mediaData['height'] = $value['upsell_image_height'];
        }
        if (isset($value['related_image_width'])) {
            $mediaData['width'] = $value['related_image_width'];
        }
        if (isset($value['related_image_height'])) {
            $mediaData['height'] = $value['related_image_height'];
        }
        if (isset($value['page_image_width'])) {
            $mediaData['width'] = $value['page_image_width'];
        }
        if (isset($value['page_image_height'])) {
            $mediaData['height'] = $value['page_image_height'];
        }
        /** thumb on category page */
        if (isset($value['grid_hover_thumb_width'])) {
            $mediaData['width'] = $value['grid_hover_thumb_width'];
        }
        if (isset($value['grid_hover_thumb_height'])) {
            $mediaData['height'] = $value['grid_hover_thumb_height'];
        }

        return $mediaData;
    }



}
