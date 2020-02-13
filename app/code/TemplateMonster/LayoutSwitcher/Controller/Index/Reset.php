<?php

namespace TemplateMonster\LayoutSwitcher\Controller\Index;

use TemplateMonster\LayoutSwitcher\Helper\Data as LayoutSwitcherHelper;
use TemplateMonster\LayoutSwitcher\Controller\Index as BaseIndex;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\Action\Context;

/**
 * Reset controller action.
 *
 * @package TemplateMonster\LayoutSwitcher\Controller\Index
 */
class Reset extends Index
{
    /**
     * @var string
     */
    protected $_successMessage = 'Customization settings has been successfully reset.';

    /**
     * Get default layouts.
     *
     * @return array
     */
    protected function _getLayouts()
    {
        return $this->_helper->getDefaultLayouts();
    }

    /**
     * Get default store code.
     *
     * @return string
     */
    protected function _getWebsiteCode()
    {
        return $this->_helper->getCurrentTheme();
    }

    /**
     * Get color scheme.
     *
     * @return string
     */
    protected function _getColorScheme()
    {
        return $this->_helper->getDefaultColorScheme();
    }
}