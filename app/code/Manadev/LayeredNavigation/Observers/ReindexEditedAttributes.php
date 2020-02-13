<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Observers;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\UrlRewrite\Model\UrlRewrite;

class ReindexEditedAttributes implements ObserverInterface
{
    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    public function __construct(IndexerRegistry $indexerRegistry) {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer) {
        $indexer = $this->indexerRegistry->get('mana_filter_attribute');
        if (!$indexer->isScheduled()) {
            $attributeId = $observer->getData('data_object')->getId();
            $indexer->reindexRow($attributeId);
        }
    }
}