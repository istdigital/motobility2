<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Observers;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout;

class AddLayeredNavigationAndProductListToCmsPage implements ObserverInterface
{
    /**
     * @var Layout
     */
    protected $layout;

    public function __construct(Layout $layout) {
        $this->layout = $layout;
    }

    public function execute(Observer $observer) {
        $page = $observer->getData('page');

        if (!$page->getData('mana_add_layered_navigation_and_products')) {
            return;
        }

        $this->layout->getUpdate()->addHandle('cms_page_layered_navigation_with_product_list');
    }
}