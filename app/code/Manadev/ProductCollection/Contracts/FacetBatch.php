<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Contracts;

class FacetBatch extends Facet
{
    /**
     * @var Facet[]
     */
    protected $facets = [];
    protected $type;

    public function __construct($type) {
        parent::__construct(null);

        $this->type = $type;
    }

    public function addFacet($facet) {
        $this->facets[$facet->getAttributeId()] = $facet;
    }

    /**
     * @param array $record
     * @return Facet
     */
    public function getFacet(&$record) {
        $attributeId = $record['attribute_id'];
        unset($record['attribute_id']);

        return $this->facets[$attributeId];
    }

    public function getFacets() {
        return $this->facets;
    }

    public function getAttributeIds() {
        $result = [];

        foreach ($this->getFacets() as $facet) {
            $result[$facet->getAttributeId()] = $facet->getAttributeId();
        }

        return $result;
    }

    public function getType() {
        return $this->type;
    }

    public function getSelectedOptionIds() {
        return false;
    }
}