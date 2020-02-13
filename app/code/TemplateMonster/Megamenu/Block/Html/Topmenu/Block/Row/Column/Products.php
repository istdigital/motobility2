<?php

namespace TemplateMonster\Megamenu\Block\Html\Topmenu\Block\Row\Column;

use Magento\Framework\View\Element\Template;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;

class Products extends Entity
{
    protected $_template = 'html/topmenu/block/row/column/products.phtml';

    protected $_productHelper;

    protected $_productRepository;

    protected $_configurableResource;

    public function __construct(
        Template\Context $context,
        Product $productHelper,
        ProductRepositoryInterface $productRepository,
        Configurable $configurableResource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_productHelper= $productHelper;
        $this->_configurableResource = $configurableResource;
        $this->_productRepository = $productRepository;
    }

    public function getProducts()
    {
        return $this->getEntity()->getProducts();
    }

    public function getProductImage($product)
    {
        if ($product->getImage() == 'no_selection') {
            $product->setImage(null);
        }
        return $this->_productHelper->getImageUrl($product);
    }

    public function getProductPrice($product)
    {
        $priceRender = $this->getPriceRender();

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
                ]
            );
        }

        return $price;
    }

    /**
     * @return \Magento\Framework\Pricing\Render
     */
    protected function getPriceRender()
    {
        return $this->getLayout()->createBlock('Magento\Framework\Pricing\Render', '');
    }

    public function getProductUrl($product)
    {
        $result = $product->getProductUrl();
        $type = $product->getTypeId();
        switch ($type) {
            case 'simple':
                if ($pIds = $this->_configurableResource->getParentIdsByChild($product->getId())) {
                    if ($parentProduct = $this->_productRepository->getById($pIds[0])) {
                        $result = $parentProduct->getProductUrl();
                    }
                }
                break;
        }
        return $result;
    }
}