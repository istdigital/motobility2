<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Inventory;

use Amasty\Preorder\Block\Adminhtml\Data\Form\Element\Text;
use Magento\Backend\Block\Widget\Form\Generic;

class PreOrder extends Generic
{
    /**
     * Return current product instance
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * Get form HTML
     *
     * @return string
     */
    public function getFormHtml()
    {
        $this->generateForm();
        if (is_object($this->getForm())) {
            return $this->getForm()->getHtml();
        }

        return '';
    }

    private function generateForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('amasty_preorder_fieldset', ['legend' => __('Pre-Order')]);
        $fieldset->addType('ampreorder_mass_action', Text::class);

        $fieldset->addField(
            'amasty_preorder_note',
            'ampreorder_mass_action',
            [
                'name' => 'attributes[amasty_preorder_note]',
                'label' => __('Pre-Order Note'),
                'title' => __('Pre-Order Note'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'amasty_preorder_cart_label',
            'ampreorder_mass_action',
            [
                'name' => 'attributes[amasty_preorder_cart_label]',
                'label' => __('Pre-Order Cart Button'),
                'title' => __('Pre-Order Cart Button'),
                'required' => false
            ]
        );

        $product = $this->getProduct();
        if ($product !== null) {
            $form->setValues($product->getData());
        }

        $this->setForm($form);
    }
}
