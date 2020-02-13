<?php

namespace TemplateMonster\Parallax\Block\Adminhtml\BlockItem;

use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;

/**
 * Block item edit container.
 *
 * @package TemplateMonster\Parallax\Block\Adminhtml\BlockItem
 */
class Edit extends Container
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * Edit constructor.
     *
     * @param Registry $registry
     * @param Context  $context
     * @param array    $data
     */
    public function __construct(
        Registry $registry,
        Context $context,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_objectId = 'item_id';
        $this->_blockGroup = 'TemplateMonster_Parallax';
        $this->_controller = 'adminhtml_blockItem';

        if ($this->_isAllowedAction('TemplateMonster_Parallax::item_save')) {
            $this->buttonList->update('save', 'label', __('Save Block Item'));
            $this->buttonList->update('back', 'on_click', sprintf("location.href = '%s';", $this->getUrl('*/block/edit', [
                'block_id' => $this->_getModel()->getData('block_id')
            ])));

            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => [
                                'event' => 'saveAndContinueEdit',
                                'target' => '#edit_form'
                            ],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('TemplateMonster_Parallax::item_delete') && $this->_getModel()->getId()) {
            $this->buttonList->add(
                'delete',
                [
                    'label' => __('Delete'),
                    'onclick' => 'deleteConfirm(\'' . __(
                            'Are you sure you want to delete block item?'
                        ) . '\', \'' . $this->getUrl(
                            '*/*/delete',
                            [
                                'item_id' => $this->_getModel()->getData('item_id'),
                                'block_id' => $this->_getModel()->getData('block_id'),
                            ]
                        ) . '\')',
                    'class' => 'delete'
                ],
                -100
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getHeaderText()
    {
        if ($this->_getModel()->getId()) {
            return __("Block Item '%1'", $this->escapeHtml($this->_getModel()->getName()));
        } else {
            return __('New Block Item');
        }
    }

    /**
     * Check is allowed action.
     *
     * @param string $resource
     *
     * @return bool
     */
    protected function _isAllowedAction($resource)
    {
        return $this->_authorization->isAllowed($resource);
    }

    /**
     * Get model instance.
     *
     * @return \TemplateMonster\Parallax\Api\Data\BlockItemInterface
     */
    protected function _getModel()
    {
        return $this->_coreRegistry->registry('current_parallax_block_item');
    }
}
