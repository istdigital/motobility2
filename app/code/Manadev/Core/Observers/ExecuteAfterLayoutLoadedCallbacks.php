<?php

namespace Manadev\Core\Observers;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Manadev\Core\Helpers\LayoutHelper;

class ExecuteAfterLayoutLoadedCallbacks implements ObserverInterface
{
    /**
     * @var LayoutHelper
     */
    protected $layoutHelper;

    public function __construct(LayoutHelper $layoutHelper) {
        $this->layoutHelper = $layoutHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer) {
        $this->layoutHelper->executeAfterLayoutLoadedCallbacks();
    }
}