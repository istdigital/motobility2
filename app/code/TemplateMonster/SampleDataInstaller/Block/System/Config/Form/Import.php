<?php

namespace TemplateMonster\SampleDataInstaller\Block\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Import
 *
 * @package TemplateMonster\SampleDataInstaller\Block\System\Config\Form
 */
class Import extends Field
{
    /**
     * @inheritdoc
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        /** @var \Magento\Backend\Block\Widget\Button $buttonBlock  */
        $buttonBlock = $this->getForm()->getLayout()->createBlock('Magento\Backend\Block\Widget\Button');

        $data = [
            'id' => 'theme_options_install_button',
            'label' => $this->_getLabel(),
            'onclick' => <<<EOL
                var section = jQuery(this).closest('table');
                var params = {
                    import_files: jQuery('.import-files', section).val(),
                    is_override: jQuery('.is-override', section).val()
                };
                
                setLocation('{$this->_getInstallUrl()}?' + jQuery.param(params));
EOL
            ,
        ];

        $html = $buttonBlock->setData($data)->toHtml();

        return $html;
    }

    /**
     * Get label.
     *
     * @return \Magento\Framework\Phrase
     */
    protected function _getLabel()
    {
        return __('Import');
    }

    /**
     * Get install url.
     *
     * @return string
     */
    protected function _getInstallUrl()
    {
        $params = [
            'website' => $this->getRequest()->getParam('website'),
            'store' => $this->getRequest()->getParam('store'),
            'type' => $this->getData('import_type')
        ];

        return $this->_urlBuilder->getUrl('sample_data_installer/import', $params);
    }
}