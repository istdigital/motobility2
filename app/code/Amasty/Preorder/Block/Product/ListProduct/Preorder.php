<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Block\Product\ListProduct;

use Amasty\Preorder\Block\Product\View\Preorder\ProductConfigurable;
use Magento\Framework\View\Element\Template;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use \Magento\ConfigurableProduct\Model\Product\Type\Configurable as ProductTypeConfigurable;

class Preorder extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'product/list/preorder.phtml';

    /**
     * @var AbstractCollection
     */
    private $productCollection;

    /**
     * @var \Amasty\Preorder\Helper\Data
     */
    private $helper;

    /**
     * @var ProductConfigurable
     */
    private $blockConfigurable;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    public function __construct(
        Template\Context $context,
        \Amasty\Preorder\Helper\Data $helper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        ProductConfigurable $blockConfigurable,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->blockConfigurable = $blockConfigurable;
        $this->jsonEncoder = $jsonEncoder;
    }

    public function _toHtml()
    {
        if ($this->getProductCollection() == null) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return AbstractCollection
     */
    public function getProductCollection()
    {
        return $this->productCollection;
    }

    /**
     * @param $productCollection
     * @return $this
     */
    public function setProductCollection($productCollection)
    {
        $this->productCollection = $productCollection;

        return $this;
    }

    /**
     * @return bool|string
     */
    public function generateJsonConfig()
    {
        $config = [];

        foreach ($this->getProductCollection() as $product) {
            if ($this->helper->getIsProductPreorder($product)) {
                $config[$product->getId()] = [
                    'cart_label' => $this->helper->getProductPreorderCartLabel($product)
                ];

                if ($this->isShowPreOrderNote()) {
                    $config[$product->getId()]['note'] = $this->helper->getProductPreorderNote($product);
                }
            }

            if ($product->getTypeId() == ProductTypeConfigurable::TYPE_CODE) {
                $config[$product->getId()]['configurable'] = $this->generateConfigurableConfig($product);
            }
        }

        return $this->jsonEncoder->encode($config);
    }

    /**
     * @param $product
     * @return array
     */
    private function generateConfigurableConfig($product)
    {
        $this->blockConfigurable->setProduct($product);
        $productId = $product->getId();

        return [
            'entity' => $productId,
            'swatchOpt' => '.swatch-opt-' . $productId,
            'addToCartLabel' => $this->helper->getProductPreorderCartLabel($product),
            'map' => $this->blockConfigurable->getProductPreorderMap(),
            'currentAttributes' =>
                [$productId => $this->blockConfigurable->getConfigurableAttributes()]
        ];
    }

    /**
     * @return bool
     */
    private function isShowPreOrderNote()
    {
        return (bool)$this->helper->isPreOrderNoteShow();
    }
}
