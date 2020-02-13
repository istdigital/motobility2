<?php

namespace TemplateMonster\LayoutSwitcher\Model\StoreResolver\Plugin;

use TemplateMonster\LayoutSwitcher\Helper\Data as LayoutSwitcherHelper;
use TemplateMonster\LayoutSwitcher\Model\StoreResolver\Store as StoreResolver;
use Magento\Store\Model\StoreResolver\ReaderList as BaseReaderList;

/**
 * Class ReaderList
 *
 * @package TemplateMonster\LayoutSwitcher\Model\StoreResolver\Plugin
 */
class ReaderList
{
    /**
     * @var LayoutSwitcherHelper
     */
    protected $_helper;

    /**
     * @var StoreResolver
     */
    protected $_storeResolver;

    /**
     * Livedemo flag.
     *
     * @var bool
     */
    protected $_livedemoMode;

    /**
     * ReaderList constructor.
     *
     * @param LayoutSwitcherHelper $helper
     * @param StoreResolver        $storeResolver
     */
    public function __construct(
        LayoutSwitcherHelper $helper,
        StoreResolver $storeResolver,
        $livedemoMode = false
    )
    {
        $this->_helper = $helper;
        $this->_storeResolver = $storeResolver;
        $this->_livedemoMode = $livedemoMode;
    }

    /**
     * @param BaseReaderList $subject
     * @param callable       $proceed
     * @param string         $runMode
     *
     * @return StoreResolver
     */
    public function aroundGetReader(BaseReaderList $subject, callable $proceed, $runMode)
    {
        if ($this->_helper->isEnabled() && $this->_livedemoMode) {
            return $this->_storeResolver;
        }

        return $proceed($runMode);
    }
}