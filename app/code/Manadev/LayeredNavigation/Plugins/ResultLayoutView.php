<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Plugins;

use Magento\Framework\View\Page;
use Magento\Framework\View\Result\Layout as LayoutResultView;
use Magento\Framework\App\ResponseInterface;
use Manadev\LayeredNavigation\Configuration;
use Manadev\LayeredNavigation\Engine;

class ResultLayoutView
{
    /**
     * @var Page\Config
     */
    protected $pageConfig;
    /**
     * @var Engine
     */
    protected $engine;
    /**
     * @var Configuration
     */
    protected $configuration;

    public function __construct(Page\Config $pageConfig, Engine $engine, Configuration $configuration) {
        $this->pageConfig = $pageConfig;
        $this->engine = $engine;
        $this->configuration = $configuration;
    }

    public function beforeRenderResult(LayoutResultView $view, ResponseInterface $response) {
        if (!$this->configuration->dontIndexMultipleFilters()) {
            return;
        }

        if ($this->isAnyFilterHasMoreThanSpecifiedNumberOfAppliedOptions(1)) {
            $this->pageConfig->setRobots('NOINDEX,NOFOLLOW');
        }
    }

    protected function isAnyFilterHasMoreThanSpecifiedNumberOfAppliedOptions($maxAppliedOptions) {
        foreach ($this->engine->getFilters() as $engineFilter) {
            $appliedOptions = $engineFilter->getAppliedOptions();
            $count = is_array($appliedOptions) ? count($appliedOptions) : 1;
            if ($appliedOptions === false || $count <= $maxAppliedOptions) {
                continue;
            }

            return true;
        }

        return false;
    }
}