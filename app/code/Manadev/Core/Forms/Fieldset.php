<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Forms;

use Magento\Framework\Data\Form\Element\Fieldset as BaseFieldset;

use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Escaper;

class Fieldset extends BaseFieldset
{
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }
    
    public function addField($elementId, $type, $config, $after = false, $isAdvanced = false)
    {
        $element = parent::addField($elementId, $type, $config, $after, $isAdvanced);
        $element->setRenderer($this->getFormBlock()->getFieldRenderer());
        return $element;
    }

    /**
     * @return \Manadev\Core\Blocks\Adminhtml\Form
     */
    public function getFormBlock() {
        return $this->getData('form_block');
    }

}