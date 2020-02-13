<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Plugins;

use Magento\Framework\View\Layout;
use Manadev\Core\Configuration;
use Manadev\Core\LayoutLogger;

class LayoutPlugin
{
    /**
     * @var Configuration
     */
    protected $configuration;
    /**
     * @var LayoutLogger
     */
    protected $layoutLogger;

    public function __construct(Configuration $configuration, LayoutLogger $layoutLogger) {
        $this->configuration = $configuration;
        $this->layoutLogger = $layoutLogger;
    }

    public function afterGenerateXml(Layout $layout, $result) {
        if ($this->configuration->isLayoutXmlLoggingEnabled()) {
            $this->layoutLogger->log($layout);
        }
        return $result;
    }
}