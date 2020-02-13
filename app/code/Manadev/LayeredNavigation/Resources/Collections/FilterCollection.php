<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Resources\Collections;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Manadev\LayeredNavigation\Models\Filter;

class FilterCollection extends AbstractCollection {
    protected $byParamName;

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Manadev\LayeredNavigation\Models\Filter', 'Manadev\LayeredNavigation\Resources\FilterResource');
    }

    public function systemWide() {
        $this->getSelect()->where("`main_table`.`store_id` = ?", 0);

        return $this;
    }

    public function storeSpecific($storeId) {
        $this->getSelect()->where("`main_table`.`store_id` = ?", $storeId);

        return $this;
    }

    public function paramName($paramName) {
        $this->getSelect()->where("`main_table`.`param_name` = ?", $paramName);

        return $this;
    }

    public function orderByPosition() {
        $this->getSelect()->order('position ASC');

        return $this;
    }

    /**
     * @return Filter[]
     */
    public function getAllByParamName() {
        if (!$this->byParamName) {
            $this->byParamName = [];

            foreach ($this as $filter) {
                $this->byParamName[$filter->getData('param_name')] = $filter;
            }
        }

        return $this->byParamName;
    }
}