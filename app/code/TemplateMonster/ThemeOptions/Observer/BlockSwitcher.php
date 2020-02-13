<?php

namespace TemplateMonster\ThemeOptions\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class BlockSwitcher implements ObserverInterface
{

    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    protected $_scopeConfig;

    /**
     * Allowed blocks in sidebar.
     *
     * @var array
     */
    protected static $blocks = [
        'compare' => [
            'name' => 'catalog.compare.sidebar',
            'path' => 'theme_options/sidebar/compare'
        ],
        'wishlist' => [
            'name' => 'wishlist_sidebar',
            'path' => 'theme_options/sidebar/wishlist'
        ],
        'orders' => [
            'name' => 'sale.reorder.sidebar',
            'path' => 'theme_options/sidebar/orders'
        ]
    ];

    /**
     * Data constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig) {
        $this->_scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();
        foreach (self::$blocks as $block) {
            $currBlock = $layout->getBlock($block['name']);
            if ($currBlock) {
                $enable = $this->_scopeConfig->getValue(
                    $block['path'],
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                if (!$enable) {
                    $layout->unsetElement($block['name']);
                }
            }
        }
    }


}