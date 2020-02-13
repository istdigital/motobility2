<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
namespace Manadev\Core\Plugins;

use Closure;
use Magento\Framework\App\Route\Config\Reader;
use Magento\Store\Model\StoreManagerInterface;
use Manadev\Core\Features;

class RouteConfigReader
{
    /**
     * @var Features
     */
    protected $features;

    public function __construct(Features $features) {
        $this->features = $features;
    }

    public function aroundRead(Reader $subject, Closure $proceed, $scope = null) {
        $output = $proceed($scope);

        foreach ($output as &$router) {
            foreach(array_keys($router['routes']) as $routeKey) {
                foreach($router['routes'][$routeKey]['modules'] as $module) {
                    if(!$this->features->isEnabled($module, 0)) {
                        unset($router['routes'][$routeKey]);
                        break;
                    }
                }
            }
        }

        return $output;
    }
}