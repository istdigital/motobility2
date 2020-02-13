<?php

namespace TemplateMonster\ThemeOptions\Controller\Css;

use Magento\Framework\View\LayoutInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * CSS Index Controller.
 *
 * @package TemplateMonster\ThemeOptions\Controller\Css
 */
class Index extends Action
{
    /**
     * @var LayoutInterface
     */
    protected $_layout;

    /**
     * @var string
     */
    protected $_stylesBlock = 'TemplateMonster\ThemeOptions\Block\View\Styles';

    /**
     * Index constructor.
     *
     * @param LayoutInterface $layout
     * @param Context         $context
     */
    public function __construct(
        LayoutInterface $layout,
        Context $context
    )
    {
        $this->_layout = $layout;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $resultPage->setHeader('Content-type', 'text/css');
        $resultPage->setContents($this->_getStyles());

        return $resultPage;
    }

    /**
     * Get dynamic styles.
     *
     * @return string
     */
    protected function _getStyles()
    {
        return $this->_layout->createBlock($this->_stylesBlock)->toHtml();
    }
}
