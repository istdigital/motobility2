<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */namespace Manadev\Core\Plugins;

use Closure;
use Magento\Framework\Indexer\Config\Reader;
use Manadev\Core\Features;

class IndexerConfigReaderModel {
    /**
     * @var Features
     */
    protected $features;

    public function __construct(Features $features) {
        $this->features = $features;
    }

    public function aroundRead(Reader $subject, Closure $proceed, $scope = null){
        $output = $proceed($scope);

        foreach($output as $key => $data) {
            if(!$this->features->isEnabled($data['action_class'], 0)) {
                unset($output[$key]);
            }
        }

        return $output;
    }
}