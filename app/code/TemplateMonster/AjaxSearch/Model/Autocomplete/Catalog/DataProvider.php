<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace TemplateMonster\AjaxSearch\Model\Autocomplete\Catalog;

use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\Autocomplete\ItemFactory;
use Magento\Search\Model\Autocomplete\DataProviderInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use TemplateMonster\AjaxSearch\Helper\AjaxSearch;

class DataProvider implements DataProviderInterface
{
    /**
     * Query factory
     *
     * @var QueryFactory
     */
    protected $queryFactory;

    /**
     * Autocomplete result item factory
     *
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var CategoryCollectionFactory
     */
    protected $_categoryCollection;

    /**
     * @var AjaxSearch
     */
    protected $_helper;

    /**
     * @param QueryFactory $queryFactory
     * @param ItemFactory $itemFactory
     */
    public function __construct(
        QueryFactory $queryFactory,
        ItemFactory $itemFactory,
        CollectionFactory $categoryCollectionFactory,
        AjaxSearch $helper
    ) {
        $this->queryFactory = $queryFactory;
        $this->itemFactory = $itemFactory;
        $this->_categoryCollection = $categoryCollectionFactory;
        $this->_helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $categorySearchStatus = $this->_helper->getCategorySearchStatus();
        $categorySearchNumResult = $this->_helper->getCategorySearchNumResult();

        if (!$categorySearchStatus || ($categorySearchNumResult <= 0)) {
            return [];
        }

        $query = $this->queryFactory->get()->getQueryText();
        $categoryCollection = $this->_categoryCollection->create();
        $categoryCollection->addFieldToSelect(['name']);
        $categoryCollection->addFieldToFilter('name', ['like'=>'%'.$query.'%']);
        $categoryCollection->addFieldToFilter('is_active', ['eq'=>true]);
        $categoryCollection->setPageSize($categorySearchNumResult);
        $categoryCollection->setCurPage(1);

        $result = [];
        foreach ($categoryCollection->getItems() as $category) {
            $resultItem = $this->itemFactory->create([
                'title' => $category->getName(),
                'url'=>$category->getUrl(),
                'category'=>true
            ]);
            if ($resultItem->getTitle() == $query) {
                array_unshift($result, $resultItem);
            } else {
                $result[] = $resultItem;
            }
        }

        return $result;
    }
}
