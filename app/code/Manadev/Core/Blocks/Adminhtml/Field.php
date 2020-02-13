<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Blocks\Adminhtml;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Field extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var string
     */
    protected $_template = 'Manadev_Core::field.phtml';

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;

        parent::__construct($context, $data);
    }

    public function render(AbstractElement $element) {
        $this->_element = $element;
        if($this->usedDefault()) {
            $element->setData('disabled', 'disabled');
        }

        return $this->toHtml();
    }

    public function canDisplayUseDefault() {
        $canUseDefault = false;
        if(!$this->getRequest()->getParam('store') && $this->_element->getData('default_label')) {
            $canUseDefault = true;
        }
        if($this->getRequest()->getParam('store') && $this->_element->getData('default_store_label')) {
            $canUseDefault = true;
        }
        return $canUseDefault && !$this->_element->getReadonly();
    }

    public function usedDefault() {
        if(!$this->canDisplayUseDefault()) {
            return false;
        }

        return $this->_element->getData('use_default');
    }

    public function useDefaultLabel(){
        $data = ($this->getRequest()->getParam('store')) ? 'default_store_label': 'default_label';
        return $this->_element->getData($data);
    }
}
