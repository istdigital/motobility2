<?php
namespace TemplateMonster\Megamenu\Model\Configurator\Row\Column;

use Magento\Framework\Api\SearchCriteriaBuilder;

use Magento\Catalog\Api\ProductRepositoryInterface;

class Products extends Entity
{
    public $rendererClass = 'Products';

    public $_products;

    protected $_productRepository;

    protected $_searchCriteriaBuilder;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_productRepository = $productRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
    }


    public function getProducts()
    {
        if (!$this->_products) {
            $products = \Zend_Json::decode($this->getValue(), true);
            asort($products);
            $searchCriteria = $this->_searchCriteriaBuilder->addFilter('entity_id', array_keys($products), 'in')->create();
            $loadedItems = $this->_productRepository->getList($searchCriteria)->getItems();
            $result = [];
            foreach ($products as $key => $value) {
                if (array_key_exists($key, $loadedItems)) {
                    $result[] = $loadedItems[$key];
                }
            }
            $this->_products = $result;
        }
        return $this->_products;
    }

}