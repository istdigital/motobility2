<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Models;

use Magento\Framework\Model\AbstractModel;
use Manadev\LayeredNavigation\Resources\Indexers\FilterIndexer;
use Manadev\LayeredNavigation\Resources\FilterResource;

class Filter  extends AbstractModel {
    /**
     * @var FilterIndexer
     */
    protected $indexer;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        FilterIndexer $indexer,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->indexer = $indexer;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Manadev\LayeredNavigation\Resources\FilterResource');
    }

    /**
     * @return FilterResource
     */
    public function getResource() {
        return parent::getResource();
    }

    public function edit(array $data) {
        $this->getResource()->edit($this, $data);
    }
    
    public function loadEdit(){
        return $this->getResource()->loadEdit($this);
    }
    
    public function loadDefaults() {
        return $this->indexer->loadDefaults($this);
    }

    public function addEditedData(&$edit, $defaults, $data) {
        $this->getResource()->addEditedData($this, $edit, $defaults, $data);
    }

    public function afterEdit() {
        $this->indexer->reindexChangedFilters([$this->getData('filter_id') => "'" . $this->getData('unique_key') . "'"], 
            $this->getData('store_id'));
    }
    
    public function getFields() {
        return $this->getResource()->getFields($this);
    }
}