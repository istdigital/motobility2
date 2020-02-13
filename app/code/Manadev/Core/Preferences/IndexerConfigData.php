<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
namespace Manadev\Core\Preferences;

class IndexerConfigData extends \Magento\Indexer\Model\Config\Data
{

    /**
     * Delete all states that are not in configuration
     *
     * @return void
     */
    protected function deleteNonexistentStates() {
        foreach ($this->stateCollection->getItems() as $state) {
            /** @var \Magento\Indexer\Model\Indexer\State $state */
            if (
                !isset($this->_data[$state->getIndexerId()]) &&
                strpos($state->getIndexerId(), "mana") !== 0
            ) {
                $state->delete();
            }
        }
    }
}