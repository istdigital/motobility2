<?php
namespace TemplateMonster\Megamenu\Plugin\Block;

use Magento\Catalog\Plugin\Block\Topmenu as TopmenuPlugin;
use Magento\Catalog\Model\Category;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Tree\Node;

class Topmenu extends TopmenuPlugin
{

    protected $_myStoreManager;

    protected $_myCollectionFactory;

    protected $_myLayerResolver;

    public function __construct(
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Catalog\Model\ResourceModel\Category\StateDependentCollectionFactory $categoryCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver
    ) {
        $this->_myCollectionFactory = $categoryCollectionFactory;
        $this->_myStoreManager = $storeManager;
        $this->_myLayerResolver= $layerResolver;
        parent::__construct($catalogCategory, $categoryCollectionFactory, $storeManager, $layerResolver);
    }

    public function beforeGetHtml(
        \Magento\Theme\Block\Html\Topmenu $subject,
        $outermostClass = '',
        $childrenWrapClass = '',
        $limit = 0
    ) {
        $rootId = $this->_myStoreManager->getStore()->getRootCategoryId();
        $storeId = $this->_myStoreManager->getStore()->getId();
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->getCategoryTree($storeId, $rootId);
        $currentCategory = $this->getMyCurrentCategory();
        $mapping = [$rootId => $subject->getMenu()];  // use nodes stack to avoid recursion
        foreach ($collection as $category) {
            if (!isset($mapping[$category->getParentId()])) {
                continue;
            }
            /** @var Node $parentCategoryNode */
            $parentCategoryNode = $mapping[$category->getParentId()];

            $categoryNode = new Node(
                $this->getMyCategoryAsArray($category, $currentCategory),
                'id',
                $parentCategoryNode->getTree(),
                $parentCategoryNode
            );
            $parentCategoryNode->addChild($categoryNode);

            $mapping[$category->getId()] = $categoryNode; //add node in stack
        }
    }

    protected function getMyCurrentCategory()
    {
        $catalogLayer = $this->_myLayerResolver->get();

        if (!$catalogLayer) {
            return null;
        }

        return $catalogLayer->getCurrentCategory();
    }

    protected function getMyCategoryAsArray($category, $currentCategory)
    {
        return [
            'name' => $category->getName(),
            'id' => 'category-node-' . $category->getId(),
            'url' => $this->catalogCategory->getCategoryUrl($category),
            'has_active' => in_array((string)$category->getId(), explode('/', $currentCategory->getPath()), true),
            'is_active' => $category->getId() == $currentCategory->getId(),
            'mm_turn_on' => $category->getMmTurnOn(),
            'mm_image' => $category->getMmImage(),
            'mm_label' => $category->getMmLabel(),
            'mm_css_class' => $category->getMmCssClass(),
            'mm_configurator' => $category->getMmConfigurator(),
            'mm_show_subcategories' => $category->getMmShowSubcategories(),
            'mm_number_of_subcategories' => $category->getMmNumberOfSubcategories(),
            'mm_view_mode' => $category->getMmViewMode()
        ];
    }

    protected function getCategoryTree($storeId, $rootId)
    {
        $collection = parent::getCategoryTree($storeId, $rootId);

        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('mm_turn_on');
        $collection->addAttributeToSelect('mm_image');
        $collection->addAttributeToSelect('mm_label');
        $collection->addAttributeToSelect('mm_css_class');
        $collection->addAttributeToSelect('mm_configurator');
        $collection->addAttributeToSelect('mm_show_subcategories');
        $collection->addAttributeToSelect('mm_number_of_subcategories');
        $collection->addAttributeToSelect('mm_view_mode');

        return $collection;
    }

}
