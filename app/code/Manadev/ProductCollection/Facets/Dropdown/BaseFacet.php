<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Facets\Dropdown;

use Manadev\ProductCollection\Contracts\Facet;

abstract class BaseFacet extends Facet
{
    /**
     * @var
     */
    protected $attributeId;

    /**
     * @var
     */
    protected $selectedOptionIds;
    /**
     * @var
     */
    protected $hideWithSingleVisibleItem;
    /**
     * @var bool
     */
    protected $showSelectedOptionsFirst;
    /**
     * @var string
     */
    protected $sortBy;

    public function __construct($name, $attributeId, $selectedOptionIds, $hideWithSingleVisibleItem,
        $showSelectedOptionsFirst, $sortBy)
    {
        parent::__construct($name);
        $this->attributeId = $attributeId;
        $this->selectedOptionIds = $selectedOptionIds;
        $this->hideWithSingleVisibleItem = $hideWithSingleVisibleItem;
        $this->showSelectedOptionsFirst = $showSelectedOptionsFirst;
        $this->sortBy = $sortBy;
    }

    /**
     * @return mixed
     */
    public function getAttributeId() {
        return $this->attributeId;
    }

    /**
     * @return mixed
     */
    public function getSelectedOptionIds() {
        return $this->selectedOptionIds;
    }

    public function getHideWithSingleVisibleItem() {
        return $this->hideWithSingleVisibleItem;
    }

    /**
     * @return bool
     */
    public function isShowSelectedOptionsFirst() {
        return $this->showSelectedOptionsFirst;
    }

    /**
     * @return string
     */
    public function getSortBy() {
        return $this->sortBy;
    }
}
