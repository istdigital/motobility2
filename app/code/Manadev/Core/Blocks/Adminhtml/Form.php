<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Blocks\Adminhtml;

use Magento\Backend\Block\Widget\Form\Generic as BaseForm;
use Magento\Framework\ObjectManagerInterface;

class Form extends BaseForm
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        ObjectManagerInterface $objectManager,
        array $data
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->objectManager = $objectManager;
    }

    /**
     * @param array $data
     * @return \Manadev\Core\Forms\Form
     */
    public function createForm(array $data = []) {
        if (!isset($data['data'])) {
            $data['data'] = [];
        }
        $data['data']['form_block'] = $this;
        return $this->objectManager->create('Manadev\Core\Forms\Form', $data);
    }

    /**
     * @return \Manadev\Core\Blocks\Adminhtml\FieldRenderer
     */
    public function getFieldRenderer() {
        return $this->_layout->getBlock('manadev.field.renderer');
    }
}