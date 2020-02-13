<?php

namespace TemplateMonster\ThemeOptions\Block\Adminhtml\System\Config\Field;

use TemplateMonster\ThemeOptions\Block\Widget\Grid\Column\Renderer\Image as ImageRenderer;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Backend system config array field renderer.
 */
class ImageSocialIcons extends AbstractFieldArray
{
    /**
     * @var ImageRenderer
     */
    protected $_imageRenderer;


    /**
     * ImageSocialIcons constructor.
     *
     * @param ImageRenderer $imageRenderer
     * @param Context       $context
     * @param array         $data
     */
    public function __construct(
        ImageRenderer $imageRenderer,
        Context $context,
        array $data = []
    )
    {
        $this->_imageRenderer = $imageRenderer;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn('social_url', ['label' => __('Social URL'), 'size' => '70']);
        $this->addColumn('width',      ['label' => __('Width'), 'size' => '10']);
        $this->addColumn('alt_text',   ['label' => __('Alt Text'), 'size' => '20']);
        $this->addColumn('image', [
            'label' => __('Image'),
            'renderer' => $this->_imageRenderer,
            'style' => 'width: 50px;'
        ]);
        $this->_addAfter = false;
    }

    protected function _getCellInputElementName($columnName)
    {
        if ($columnName === 'image') {
            return $this->getElement()->getName();
        }

        return parent::_getCellInputElementName($columnName);
    }
}
