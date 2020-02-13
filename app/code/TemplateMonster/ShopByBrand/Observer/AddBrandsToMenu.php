<?php
/**
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace TemplateMonster\ShopByBrand\Observer;

use TemplateMonster\ShopByBrand\Helper\Data as BrandHelper;
use TemplateMonster\ShopByBrand\Api\Data\BrandInterfaceFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\UrlInterface;

/**
 * Class AddBrandsToMenu.
 */
class AddBrandsToMenu implements ObserverInterface
{
    /**
     * @var BrandInterfaceFactory
     */
    protected $_brandFactory;

    /**
     * @var BrandHelper
     */
    protected $_helper;

    /**
     * @var NodeFactory
     */
    protected $_nodeFactory;

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var null|\TemplateMonster\ShopByBrand\Model\ResourceModel\Brand\Collection
     */
    private $_brandCollection = null;

    /**
     * AddBrandsToMenu constructor.
     *
     * @param BrandInterfaceFactory $brandFactory
     * @param BrandHelper           $helper
     * @param NodeFactory           $nodeFactory
     * @param UrlInterface          $urlBuilder
     */
    public function __construct(
        BrandInterfaceFactory $brandFactory,
        BrandHelper $helper,
        NodeFactory $nodeFactory,
        UrlInterface $urlBuilder
    ) {
        $this->_brandFactory = $brandFactory;
        $this->_helper = $helper;
        $this->_nodeFactory = $nodeFactory;
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        if (!$this->_isNeedToAddMenuItem()) {
            return;
        }

        $menu = $observer->getData('menu');

        $rootNode = $this->_createRootNode($menu);
        foreach ($this->_getBrandCollection() as $item) {
            $this->_addBrandNode($rootNode, $item);
        }
        if ($this->_isBrandsAmountExceedsLimit()) {
            $this->_addViewAllBrandsNode($rootNode, $menu);
        }

        $children = $menu->getChildren();
        $children->add($rootNode);
    }

    /**
     * Check if need to add menu item.
     *
     * @return bool
     */
    protected function _isNeedToAddMenuItem()
    {
        return $this->_helper->isShowTopLink() && ($this->_getBrandsCount() > 0);
    }

    /**
     * Get brand collection.
     *
     * @return mixed
     */
    protected function _getBrandCollection()
    {
        if (null === $this->_brandCollection) {
            /** @var \TemplateMonster\ShopByBrand\Model\ResourceModel\Brand\Collection $collection */
            $collection = $this->_brandFactory->create()->getResourceCollection();
            $collection->addWebsiteFilter()->addEnabledFilter();
            if ($this->_helper->topLinkNumber()) {
                $collection->setPageSize($this->_helper->topLinkNumber());
            }

            $this->_brandCollection = $collection;
        }

        return $this->_brandCollection;
    }

    /**
     * Get brands count.
     *
     * @return int
     */
    protected function _getBrandsCount()
    {
        return $this->_getBrandCollection()->getSize();
    }

    /**
     * @param $menu
     *
     * @return array
     */
    protected function _createRootNode($menu)
    {
        return $this->_nodeFactory->create([
            'idField' => 'id',
            'data' => [
                'name' => __('Brands'),
                'id' => 'brand-1',
                'url' => $this->_urlBuilder->getUrl('brand'),
            ],
            'tree' => $menu->getTree(),
        ]);
    }

    /**
     * @param $rootNode
     * @param $item
     */
    protected function _addBrandNode($rootNode, $item)
    {
        $node = $this->_nodeFactory->create([
            'idField' => 'id',
            'data' => [
                'name' => $item->getName(),
                'id' => $item->getUrlPage().'-'.$item->getId(),
                'url' => $item->getUrl(),
            ],
            'tree' => $rootNode->getTree(),
            $rootNode,
        ]);

        $rootNode->addChild($node);
    }

    /**
     * @param $rootNode
     * @param $menu
     */
    protected function _addViewAllBrandsNode($rootNode, $menu)
    {
        $node = $this->_nodeFactory->create([
            'idField' => 'id',
            'data' => [
                'name' => __('View All Brands'),
                'id' => 'all-brands-1',
                'url' => $this->_urlBuilder->getUrl('brand'),
            ],
            'tree' => $menu->getTree(),
        ]);

        $rootNode->addChild($node);
    }

    /**
     * @return bool
     */
    protected function _isBrandsAmountExceedsLimit()
    {
        if (!$this->_helper->topLinkNumber()) {
            return false;
        }

        return $this->_getBrandsCount() > $this->_helper->topLinkNumber();
    }
}
