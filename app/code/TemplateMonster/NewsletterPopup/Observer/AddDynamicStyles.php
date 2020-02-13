<?php

namespace TemplateMonster\NewsletterPopup\Observer;

use Magento\Framework\UrlInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Add dynamic styles link.
 *
 * @package TemplateMonster\NewsletterPopup\Observer
 */
class AddDynamicStyles implements ObserverInterface
{
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var array
     */
    protected $_cssOptions = [
        'content_type' => 'link',
        'rel' => 'stylesheet',
        'type' => 'text/css',
        'src_type' => 'controller',
        'media' => 'all'
    ];

    /**
     * AddDynamicStyles constructor.
     *
     * @param UrlInterface $urlBuilder
     */
    public function __construct(UrlInterface $urlBuilder)
    {
        $this->_urlBuilder = $urlBuilder;
        $this->_cssOptions['src'] = $this->_getCssUrl();
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getData('layout');

        $pageConfig = $layout->getReaderContext()->getPageConfigStructure();
        $pageConfig->addAssets($this->_getCssUrl(), $this->_cssOptions);
    }

    /**
     * Get CSS url.
     *
     * @return string
     */
    protected function _getCssUrl()
    {
        return $this->_urlBuilder->getUrl('newsletter_popup/css/index');
    }
}