<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
namespace Manadev\Core\Plugins;

use Closure;
use Magento\Backend\Model\Menu\Config\Reader;
use Manadev\Core\Features;

class MenuConfigReaderModel {
    /**
     * @var Features
     */
    protected $features;

    public function __construct(Features $features) {
        $this->features = $features;
    }

    public function aroundRead(Reader $subject, Closure $proceed, $scope = null){
        $output = $proceed($scope);

        foreach(array_keys($output) as $key) {
            if(!isset($output[$key]['module'])) {
                continue;
            }

            if(!$this->features->isEnabled($output[$key]['module'], 0)){
                unset($output[$key]);
            }
        }

        return $output;
    }
}