<?php

namespace TemplateMonster\ThemeOptions\Plugin\ConfigurableProduct\Block\Product\View\Type;

class ConfigurablePlugin
{
    protected $_helper;

    protected $_configurableHelper;

    public function __construct(
        \TemplateMonster\ThemeOptions\Helper\Data $helper,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\ConfigurableProduct\Helper\Data $configurableHelper
    ) {
        $this->_helper = $helper;
        $this->_imageHelper = $context->getImageHelper();
        $this->_configurableHelper = $configurableHelper;
    }

    public function beforeGetJsonConfig($data)
    {
        foreach ($data->getAllowProducts() as $product) {
            $productImages = $this->_configurableHelper->getGalleryImages($product) ?: [];
            foreach ($productImages as $image) {
                $this->_imageHelper->init($product, 'product_base_image')
                    ->setImageFile($image->getFile())
                    ->constrainOnly(true)
                    ->keepAspectRatio(true)
                    ->keepTransparency(true)
                    ->keepFrame(true)
                    ->resize($this->_helper->getProductGalleryImgWidth(), $this->_helper->getProductGalleryImgHeight())
                    ->getUrl();
            }
        }
    }
}
