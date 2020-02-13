<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Blocks\Adminhtml\ExtensionControl;

use Magento\Backend\Block\Widget\Form\Container;

class EditContainer extends Container {

    /**
     * @var string
     */
    protected $_blockGroup = 'Manadev_Core';

    protected function _construct() {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Manadev_Core';
        $this->_controller = 'adminhtml_extensionControl';

        parent::_construct();

        $this->buttonList->remove('reset');
        $this->buttonList->remove('back');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('save');

        $this->addButton('update_version', ['label' => __('Check for Updates'), 'class' => 'primary'], 1, 0, 'toolbar');

        $this->_formScripts[] = "
            require(['jquery'], function ($) {
                $('#update_version').on('click', function() {
                    location.href = '{$this->getUrl('*/*/updateVersion', ['store' => $this->_request->getParam('store')])}'
                });
                $('.col-is_enabled select').on('change', function() {
                    $('#block_feature').val($(this).data('feature'));
                    $('#block_is_enabled').val($(this).val());
                    $('#edit_form').submit();
                });
            });
        ";
    }

    protected function _buildFormClassName() {
        return $this->nameBuilder->buildClassName([$this->_blockGroup, 'Blocks', $this->_controller,
            $this->_mode . 'Form']);
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    public function getSaveUrl() {
        return $this->getUrl('*/*/save', [
            '_current' => true,
            'back' => null,
            'store' => $this->getRequest()->getParam('store', 0)
        ]);
    }
}