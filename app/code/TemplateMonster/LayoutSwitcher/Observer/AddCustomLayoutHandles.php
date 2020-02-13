<?php

namespace TemplateMonster\LayoutSwitcher\Observer;

use TemplateMonster\LayoutSwitcher\Helper\Data as LayoutSwitcherHelper;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AddCustomLayoutHandles
 *
 * @package TemplateMonster\LayoutSwitcher\Observer
 */
class AddCustomLayoutHandles implements ObserverInterface
{
    /**
     * @var LayoutSwitcherHelper
     */
    protected $_helper;

    /**
     * AddFooterLayoutHandle constructor.
     *
     * @param LayoutSwitcherHelper $helper
     */
    public function __construct(LayoutSwitcherHelper $helper)
    {
        $this->_helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        if ($this->_helper->isEnabled()) {
            /** @var LayoutInterface $layout */
            $layout = $observer->getData('layout');

            foreach ($this->_helper->getLayoutHandles() as $handle => $metadata) {
                if ($this->_isApplicableHandle($layout, $metadata)) {
                    $layout->getUpdate()->addHandle($handle);
                }
            }
        }
    }

    /**
     * Check if layout handle is applicable (i.e. dependency is satisfied)
     *
     * @param LayoutInterface $layout
     * @param array           $metadata
     *
     * @return bool
     */
    protected function _isApplicableHandle(LayoutInterface $layout, $metadata)
    {
        if (empty($metadata['depends'])) {
            return true;
        }
        $path = explode(',', $metadata['depends']);
        $isHandle = false;
        foreach ($path as $value) {
            if(in_array($value, $layout->getUpdate()->getHandles(), true)) {
                $isHandle = true;
            }
        }

        return $isHandle;
    }
}