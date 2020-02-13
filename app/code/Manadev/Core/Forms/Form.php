<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Forms;

use Magento\Framework\Data\Form as BaseForm;
use Magento\Framework\ObjectManagerInterface;

class Form extends BaseForm
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Data\Form\FormKey $formKey,
        ObjectManagerInterface $objectManager,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $formKey, $data);
        $this->objectManager = $objectManager;
    }

    public function addFieldset($elementId, $config, $after = false, $isAdvanced = false)
    {
        $config['form_block'] = $this->getData('form_block');
        $element = $this->objectManager->create('Manadev\Core\Forms\Fieldset', ['data' => $config]);
        $element->setId($elementId);
        $element->setAdvanced($isAdvanced);
        $this->addElement($element, $after);
        return $element;
    }

    public function addFieldsetBefore($elementId, $config, $before = false, $isAdvanced = false)
    {
        if ($before) {
            $after = '^';
            $found = false;
            foreach ($this->getElements() as $element) {
                if ($element->getId() == $before) {
                    $found = true;
                    break;
                }
                $after = $element->getId();
            }
            if (!$found) {
                $after = false;
            }
        }
        else {
            $after = false;
        }

        return $this->addFieldset($elementId, $config, $after, $isAdvanced);
    }

    /**
     * @return \Manadev\Core\Blocks\Adminhtml\Form
     */
    public function getFormBlock() {
        return $this->getData('form_block');
    }
}