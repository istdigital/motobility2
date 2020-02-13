<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Observers;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout;
use Manadev\Core\Configuration;

class AddStyles implements ObserverInterface
{
    /**
     * @var Layout
     */
    protected $layout;
    /**
     * @var Configuration
     */
    protected $configuration;

    public function __construct(Layout $layout, Configuration $configuration) {
        $this->layout = $layout;
        $this->configuration = $configuration;
    }

    public function execute(Observer $observer) {
        if (!$this->configuration->includeCss()) {
            return;
        }

        $this->layout->getUpdate()->addHandle('mana_styles');
    }
}