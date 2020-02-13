<?php
namespace TemplateMonster\CatalogImagesGrid\Model\Config\Source;

class UseImage implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 'thumbnail_image', 'label' => __('Thumbnail Image')],
                ['value' => 'category_image', 'label' => __('Category Image')],
                ['value' => 'font_icon', 'label' => __('Custom Font Icon')]];
    }

}