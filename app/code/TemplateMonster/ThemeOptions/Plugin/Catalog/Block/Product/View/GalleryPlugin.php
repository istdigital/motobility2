<?php

namespace TemplateMonster\ThemeOptions\Plugin\Catalog\Block\Product\View;

use \Magento\Catalog\Block\Product\View\Gallery;
use \Magento\Catalog\Helper\Image;
use \TemplateMonster\ThemeOptions\Helper\Data;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Json\DecoderInterface;

/**
 * Config edit plugin.
 *
 * @package TemplateMonster\ThemeOptions\Plugin\Catalog\Block\Product\View
 */
class GalleryPlugin
{
    /**
     * ThemeOptions helper.
     *
     * @var helper
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * GalleryPlugin constructor.
     * @param Data $helper
     * @param EncoderInterface $jsonEncoder
     * @param DecoderInterface $jsonDecoder
     */

    public function __construct(
        Data $helper,
        Image $imageHelper,
        EncoderInterface $jsonEncoder,
        DecoderInterface $jsonDecoder
    ) {
        $this->_helper = $helper;
        $this->_imageHelper = $imageHelper;
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
    }


    public function aroundGetGalleryImages(Gallery $subject, callable $proceed)
    {
        $product = $subject->getProduct();
        $images = $product->getMediaGalleryImages();
        if ($images instanceof \Magento\Framework\Data\Collection) {
            foreach ($images as $image) {
                /* @var \Magento\Framework\DataObject $image */
                $image->setData(
                    'small_image_url',
                    $this->_imageHelper->init($product, 'product_page_image_small')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'medium_image_url',
                    $this->_imageHelper->init($product, 'product_page_image_medium_no_frame')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'large_image_url',
                    $this->_imageHelper->init($product, 'product_page_image_large_no_frame')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
            }
        }

        return $images;
    }

//    public function aroundGetGalleryImagesJson(Gallery $subject, callable $proceed)
//    {
//        $imagesItems = [];
//        foreach ($subject->getGalleryImages() as $image) {
//            $imagesItems[] = [
//                'thumb' => $image->getData('small_image_url'),
//                'img' => $image->getData('medium_image_url'),
//                'full' => $image->getData('large_image_url'),
//                'caption' => ($image->getLabel() ?: $subject->getProduct()->getName()),
//                'position' => $image->getPosition(),
//                'isMain' => $subject->isMainImage($image),
//                'type' => str_replace('external-', '', $image->getMediaType()),
//                'videoUrl' => $image->getVideoUrl(),
//            ];
//        }
//        if (empty($imagesItems)) {
//            $imagesItems[] = [
//                'thumb' => $this->_imageHelper->getDefaultPlaceholderUrl('thumbnail'),
//                'img' => $this->_imageHelper->getDefaultPlaceholderUrl('image'),
//                'full' => $this->_imageHelper->getDefaultPlaceholderUrl('image'),
//                'caption' => '',
//                'position' => '0',
//                'isMain' => true,
//                'type' => 'image',
//                'videoUrl' => null,
//            ];
//        }
//        return json_encode($imagesItems);
//    }

    /**
     * @param Gallery $subject
     * @param callable $proceed
     * @return string
     */
    public function aroundGetMagnifier(
        Gallery $subject,
        callable $proceed)
    {
        $magnifierArray = $this->jsonDecoder->decode($proceed());
        $magnifierArray["width"] = $this->_helper->getProductGalleryImgWidth() / 2;
        $magnifierArray["height"] = $this->_helper->getProductGalleryImgHeight() / 2;
        $magnifierArray["enabled"] = (bool) $this->_helper->isImageZoom();

        return $this->jsonEncoder->encode($magnifierArray);
    }

}