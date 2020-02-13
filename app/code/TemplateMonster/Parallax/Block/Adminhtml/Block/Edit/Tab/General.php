<?php

namespace TemplateMonster\Parallax\Block\Adminhtml\Block\Edit\Tab;

use TemplateMonster\Parallax\Api\Data\BlockInterface;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

/**
 * Block general tab.
 *
 * @package TemplateMonster\Parallax\Block\Adminhtml\Block\Edit\Tab
 */
class General extends Generic implements TabInterface
{
    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * @var Enabledisable
     */
    protected $_enabledisable;

    /**
     * General constructor.
     *
     * @param Store         $systemStore
     * @param Enabledisable $enabledisable
     * @param Context       $context
     * @param Registry      $registry
     * @param FormFactory   $formFactory
     * @param array         $data
     */
    public function __construct(
        Store $systemStore,
        Enabledisable $enabledisable,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_enabledisable = $enabledisable;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $form = $this->_buildForm();
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('General Settings');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('General Settings');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return \Magento\Framework\Data\Form
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _buildForm()
    {
        $form = $this->_formFactory->create();
        $model = $this->_getModel();

        $fieldset = $form->addFieldset('general', [
            'legend' => __('General Settings'),
        ]);

        if ($model->getId()) {
            $fieldset->addField('block_id', 'hidden', [
                'name' => 'block[block_id]',
            ]);
        }

        $fieldset->addField('name', 'text', [
            'name' => 'block[name]',
            'label' => __('Block Name'),
            'title' => __('Block Name'),
            'required' => true,
        ]);

        $fieldset->addField('is_full_width', 'select', [
            'name' => 'block[is_full_width]',
            'label' => __('Is Full Width'),
            'title' => __('Is Full Width'),
            'required' => true,
            'values' => $this->_enabledisable->toOptionArray(),
        ]);

        $fieldset->addField('status', 'select', [
            'name' => 'block[status]',
            'label' => __('Status'),
            'title' => __('Status'),
            'required' => true,
            'values' => $this->_enabledisable->toOptionArray(),
        ]);

        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField('store_id', 'multiselect', [
                'name' => 'block[stores][]',
                'label' => __('Store View'),
                'title' => __('Store View'),
                'required' => true,
                'values' => $this->_systemStore->getStoreValuesForForm(false, true),
            ]);
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField('store_id', 'hidden', [
                    'name' => 'block[store_id][]',
                    'value' => $this->_storeManager->getStore(true)->getId(),
            ]);
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $fieldset->addField('css_class', 'text', [
            'name' => 'block[css_class]',
            'label' => __('CSS-class'),
            'title' => __('CSS-class'),
        ]);


        if ($model->getId()) {
            $form->setValues($model->getData());
        }
        else {
            $form->setValues([
                'status' => BlockInterface::STATUS_ENABLED,
                'store_id' => [0]
            ]);
        }

        return $form;
    }

    /**
     * @return BlockInterface
     */
    protected function _getModel()
    {
        return $this->_coreRegistry->registry('current_parallax_block');
    }
}
