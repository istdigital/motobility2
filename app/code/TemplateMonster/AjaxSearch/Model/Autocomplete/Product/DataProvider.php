<?php

/**
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace TemplateMonster\AjaxSearch\Model\Autocomplete\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Render;
use Magento\Framework\View\LayoutInterface;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\Autocomplete\ItemFactory;
use Magento\Search\Model\Autocomplete\DataProviderInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Helper\Image;
use TemplateMonster\AjaxSearch\Helper\AjaxSearch;

class DataProvider implements DataProviderInterface
{
    /**
     * Query factory.
     *
     * @var QueryFactory
     */
    protected $queryFactory;

    /**
     * Autocomplete result item factory.
     *
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var CollectionFactory
     */
    protected $_productCollection;

    /**
     * @var Image
     */
    protected $_imageHelper;

    /**
     * @var AjaxSearch
     */
    protected $_helper;

    /**
     * @var LayoutInterface
     */
    protected $_layout;

    /**
     * DataProvider constructor.
     *
     * @param QueryFactory      $queryFactory
     * @param ItemFactory       $itemFactory
     * @param CollectionFactory $productCollection
     * @param Image             $imageHelper
     * @param AjaxSearch        $helper
     * @param LayoutInterface   $layout
     */
    public function __construct(
        QueryFactory $queryFactory,
        ItemFactory $itemFactory,
        CollectionFactory $productCollection,
        Image $imageHelper,
        AjaxSearch $helper,
        LayoutInterface $layout

    ) {
        $this->queryFactory = $queryFactory;
        $this->itemFactory = $itemFactory;
        $this->_productCollection = $productCollection;
        $this->_imageHelper = $imageHelper;
        $this->_helper = $helper;
        $this->_layout = $layout;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $productSearchStatus = $this->_helper->getProductSearchStatus();
        $productSearchNumResult = $this->_helper->getProductSearchNumResult();

        if (!$productSearchStatus || ($productSearchNumResult <= 0)) {
            return [];
        }

        $query = $this->queryFactory->get()->getQueryText();
        $productCollection = $this->_productCollection->create();
        $productCollection->addFieldToSelect(['name', 'thumbnail', 'price', 'special_price', 'sku', 'date' => true,]);
        //$productCollection->addFieldToFilter('name', ['like' => '%'.$query.'%']);
        $productCollection->addFieldToFilter(
            array(
                array('attribute'=>'name', 'like'=>'%'.$query.'%'),
                array('attribute'=>'sku', 'like'=>'%'.$query.'%')
            )

        );
        $productCollection->addFieldToFilter('visibility',
            ['in' => [Visibility::VISIBILITY_BOTH, Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_IN_SEARCH]]
        );
        $productCollection->setPageSize($productSearchNumResult);
        $productCollection->setCurPage(1);

        $result = [];
        foreach ($productCollection->getItems() as $product) {
            if ($product->getThumbnail()) {
                $img = $this->_imageHelper->init($product, 'product_page_image_small')
                    ->setImageFile($product->getThumbnail())
                    ->getUrl();
            }

            $resultItem = $this->itemFactory->create([
                'title' => $product->getName(),
                'url' => $product->getProductUrl(),
                'image' => $img,
                'sku' => $product->getSku(),
                'price' => $this->_renderPriceHtml($product),
                'product' => true,
            ]);
            if ($resultItem->getTitle() == $query || $resultItem->getSku() == $query ) {
                array_unshift($result, $resultItem);
            } else {
                $result[] = $resultItem;
            }
        }

        return $result;
    }

    /**
     * Render price html.
     *
     * @param Product $product
     *
     * @return string
     */
    protected function _renderPriceHtml(Product $product)
    {
        $price = '';
        $priceRender = $this->_getPriceRender();
        if ($priceRender) {
            $price = $priceRender->render(
                FinalPrice::PRICE_CODE,
                $product,
                [
                    'zone' => Render::ZONE_ITEM_VIEW,
                ]
            );
        }

        return $price;
    }

    /**
     * Get price render block.
     *
     * @return Render
     */
    protected function _getPriceRender()
    {
        /* @var Render $priceRender */
        $layout = $this->_layout;
        $layout->getUpdate()->addHandle('default');

        $priceRender = $layout->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->_getLayout()->createBlock(
                'Magento\Framework\Pricing\Render',
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }

        return $priceRender;
    }
}
