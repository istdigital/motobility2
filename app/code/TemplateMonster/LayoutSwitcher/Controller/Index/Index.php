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
 * Index controller action.
 *
 * @package TemplateMonster\LayoutSwitcher\Controller\Index
 */
class Index extends BaseIndex
{
    /**
     * @var LayoutSwitcherHelper
     */
    protected $_helper;

    /**
     * @var FormKeyValidator
     */
    protected $_formKeyValidator;

    /**
     * @var string
     */
    protected $_successMessage = 'Customization settings has been successfully applied.';

    /**
     * Index constructor.
     *
     * @param LayoutSwitcherHelper $helper
     * @param FormKeyValidator     $formKeyValidator
     * @param Context              $context
     */
    public function __construct(
        LayoutSwitcherHelper $helper,
        FormKeyValidator $formKeyValidator,
        Context $context
    )
    {
        $this->_helper = $helper;
        $this->_formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var Redirect $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $result->setRefererOrBaseUrl();

        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $result;
        }

        $this
            ->_helper
            ->setCurrentTheme($this->_getWebsiteCode())
            ->setCookieStore($this->_getStoreCode())
            ->setCookieLayout($this->_getLayouts())
            ->setCookieColorScheme($this->_getColorScheme());

        if ($this->_helper->isThemeChanged()) {
            $result->setPath('');
        }

        $this->messageManager->addSuccessMessage($this->_successMessage);

        return $result;
    }

    /**
     * Get layouts from the request.
     *
     * @return array
     */
    protected function _getLayouts()
    {
        $layouts = [];
        foreach ($this->_helper->getLayoutTypes() as $type) {
            $name = sprintf('%s_layout', $type);
            if ($value = $this->getRequest()->getParam($name)) {
                $layouts[$type] = $value;
            }
        }

        return $layouts;
    }

    /**
     * Get store code.
     *
     * @return string
     */
    protected function _getWebsiteCode()
    {
        return $this->getRequest()->getParam('theme');
    }

    /**
     * Get store code.
     *
     * @return string
     */
    protected function _getStoreCode()
    {
        return $this->getRequest()->getParam('homepage');
    }

    /**
     * Get color scheme.
     *
     * @return string
     */
    protected function _getColorScheme()
    {
       return $this->getRequest()->getParam('color_scheme');
    }
}