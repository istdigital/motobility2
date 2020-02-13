<?php

namespace TemplateMonster\Parallax\Block\Adminhtml\Block;

use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;

/**
 * Block edit container.
 *
 * @package TemplateMonster\Parallax\Block\Adminhtml\Block
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
        $this->_objectId = 'block_id';
        $this->_blockGroup = 'TemplateMonster_Parallax';
        $this->_controller = 'adminhtml_block';

        if ($this->_isAllowedAction('TemplateMonster_Parallax::block_save')) {
            $this->buttonList->update('save', 'label', __('Save Block'));
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
    }

    /**
     * @inheritdoc
     */
    public function getHeaderText()
    {
        if ($this->_getModel()->getId()) {
            return __("Parallax Block '%1'", $this->escapeHtml($this->_getModel()->getName()));
        } else {
            return __('New Block');
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
     * @return \TemplateMonster\Parallax\Api\Data\BlockInterface
     */
    protected function _getModel()
    {
        return $this->_coreRegistry->registry('current_parallax_block');
    }
}
