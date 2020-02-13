<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FeaturedProduct\Model\Data\Provider;


class ProductModel implements \TemplateMonster\FeaturedProduct\Api\Data\ProductModelInterface
{

    protected $_types;

    public function __construct()
    {
        $this->_types = $this->_getProductsModel();
    }

    /**
     *
     * Get model by key
     *
     * @param $type
     * @return mixed
     * @throws \Exception
     */
    public function getModelByType($type)
    {
        if(!$this->_types || !isset($this->_types[$type]))
        {
            throw new \Exception('Model does not exist.');
        }
        return $this->_types[$type];
    }

    /**
     * Get models data
     * @return array
     */
    protected function _getProductsModel()
    {
        return [
          'new_product' => 'TemplateMonster\FeaturedProduct\Model\NewProducts',
          'sale_product' => 'TemplateMonster\FeaturedProduct\Model\SaleProducts',
          'viewed_product' => 'TemplateMonster\FeaturedProduct\Model\ViewedProducts',
          'bestseller_product' => 'TemplateMonster\FeaturedProduct\Model\BestsellersProducts',
          'rated_product' => 'TemplateMonster\FeaturedProduct\Model\RatedProducts',
          'manual_product' => 'TemplateMonster\FeaturedProduct\Model\ManualProducts',
          'all_product' => 'TemplateMonster\FeaturedProduct\Model\AllProducts',
        ];
    }


}