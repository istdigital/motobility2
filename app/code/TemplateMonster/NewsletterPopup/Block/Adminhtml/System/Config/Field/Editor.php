<?php

namespace TemplateMonster\NewsletterPopup\Block\Adminhtml\System\Config\Field;

use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template;
use Magento\Config\Block\System\Config\Form\Field;

/**
 * Editor frontend model.
 *
 * @package TemplateMonster\NewsletterPopup\Block\Adminhtml\System\Config\Field
 */
class Editor extends Field
{
    /**
     * @var WysiwygConfig
     */
    protected $_wysiwygConfig;

    /**
     * Editor constructor.
     *
     * @param WysiwygConfig    $wysiwygConfig
     * @param Template\Context $context
     * @param array            $data
     */
    public function __construct(
        WysiwygConfig $wysiwygConfig,
        Template\Context $context,
        array $data = []
    )
    {
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setWysiwyg(true);
        $element->setConfig($this->_wysiwygConfig->getConfig($element));

        return parent::_getElementHtml($element);
    }
}