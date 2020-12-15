<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Helper;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Helper\Context;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Store\Model\ScopeInterface;
use Amasty\Preorder\Model\Order\ProductQty;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const BACKORDERS_PREORDER_OPTION = 101;
    const ALLOWED_TAGS = '<b><a><i><strong><blockquote><code><del><em><img><kbd><p><s><sup><sub><br><hr><ul><li><h1>'
        . '<h2><h3><dd><dl><span>';

    protected $isOrderProcessing = false;

    /**
     * @var Templater
     */
    private $templater;

    /**
     * @var \Magento\Catalog\Helper\Output
     */
    private $outputHelper;

    /**
     * @var \Amasty\Preorder\Model\ResourceModel\OrderPreorderFactory
     */
    private $orderPreorderResourceFactory;

    /**
     * @var \Amasty\Preorder\Model\OrderPreorderFactory
     */
    private $orderPreorderFactory;

    /**
     * @var \Amasty\Preorder\Model\OrderItemPreorderFactory
     */
    private $orderItemPreorderFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    private $stockRegistry;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $filterManager;

    /**
     * @var ProductQty
     */
    private $productQty;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\Preorder\Model\ResourceModel\Inventory
     */
    private $inventory;

    public function __construct(
        Context $context,
        \Amasty\Preorder\Helper\Templater $templater,
        \Magento\Catalog\Helper\Output $outputHelper,
        \Amasty\Preorder\Model\ResourceModel\OrderPreorderFactory $orderPreorderResourceFactory,
        \Amasty\Preorder\Model\OrderPreorderFactory $orderPreorderFactory,
        \Amasty\Preorder\Model\OrderItemPreorderFactory $orderItemPreorderFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Filter\FilterManager $filterManager,
        ProductQty $productQty,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Preorder\Model\ResourceModel\Inventory $inventory
    ) {
        parent::__construct($context);
        $this->templater = $templater;
        $this->outputHelper = $outputHelper;
        $this->orderPreorderResourceFactory = $orderPreorderResourceFactory;
        $this->orderPreorderFactory = $orderPreorderFactory;
        $this->orderItemPreorderFactory = $orderItemPreorderFactory;
        $this->collectionFactory = $collectionFactory;
        $this->stockRegistry = $stockRegistry;
        $this->orderFactory = $orderFactory;
        $this->filterManager = $filterManager;
        $this->productQty = $productQty;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->inventory = $inventory;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function checkNewOrder(\Magento\Sales\Model\Order $order)
    {
        /** @var \Amasty\Preorder\Model\ResourceModel\OrderPreorder $orderPreorderResource */
        $orderPreorderResource = $this->orderPreorderResourceFactory->create();
        $alreadyProcessed = $order->getId() && $orderPreorderResource->getIsOrderProcessed($order->getId());

        if (!$alreadyProcessed) {
            if ($order->getId() === null) {
                $order->save();
            }

            $this->processNewOrder($order);
        }

        // Will work for normal email flow only. Deprecated.
        if ($this->getOrderIsPreorderFlag($order)) {
            $order->setData('preorder_warning', $orderPreorderResource->getWarningByOrderId($order->getId()));
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    protected function processNewOrder(\Magento\Sales\Model\Order $order)
    {
        $this->isOrderProcessing = true;
        $orderIsPreorder = false;

        /** @var \Magento\Sales\Model\ResourceModel\Order\Item\Collection $itemCollection */
        $itemCollection = $order->getItemsCollection();

        foreach ($itemCollection as $item) {
            $this->productQty->addBackorderedQty(
                $item->getProductId(),
                $item->getQtyBackordered()
            );
            $orderItemIsPreorder = $this->getOrderItemIsPreorder($item);

            if ($orderItemIsPreorder) {
                $this->saveOrderItemPreorderFlag($item, $orderItemIsPreorder);
            }

            $orderIsPreorder |= $orderItemIsPreorder;
        }

        if ($orderIsPreorder) {
            /** @var \Amasty\Preorder\Model\OrderPreorder $orderPreorder */
            $orderPreorder = $this->orderPreorderFactory->create();
            $orderPreorder->setOrderId($order->getId());
            $orderPreorder->setIsPreorder($orderIsPreorder);
            $warningText = $this->getCurrentStoreConfig('ampreorder/general/orderpreorderwarning');
            $orderPreorder->setWarning($warningText);
            $orderPreorder->save();
        }
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $orderItem
     *
     * @return mixed
     */
    protected function getOrderItemIsPreorder(\Magento\Sales\Model\Order\Item $orderItem)
    {
        /** @var Product $product */
        $product = $orderItem->getProduct();
        $product->setQuoteItemId($orderItem->getQuoteItemId());
        $result = $this->getIsProductPreorder($product);

        if (!$result) {
            foreach ($orderItem->getChildrenItems() as $childItem) {
                $result = $this->getOrderItemIsPreorder($childItem);
                if ($result) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param $isPreorder
     */
    protected function saveOrderItemPreorderFlag(\Magento\Sales\Model\Order\Item $orderItem, $isPreorder)
    {
        /** @var \Amasty\Preorder\Model\OrderItemPreorder $orderItemPreorder */
        $orderItemPreorder = $this->orderItemPreorderFactory->create();
        $orderItemPreorder->setOrderItemId($orderItem->getId());
        $orderItemPreorder->setIsPreorder($isPreorder);
        $orderItemPreorder->setPreorderNote($this->getOrderItemPreorderNote($orderItem, false));
        $orderItemPreorder->save();
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param int $qtyMultiplier
     *
     * @return bool
     */
    public function getQuoteItemIsPreorder(\Magento\Quote\Model\Quote\Item $item, $qtyMultiplier = 1)
    {
        $product = $item->getProduct();
        $qty = $item->getQty() * $qtyMultiplier;

        if ($product->isComposite()) {
            $productTypeInstance = $product->getTypeInstance();

            if ($productTypeInstance instanceof Configurable) {
                /** @var Configurable $productTypeInstance */

                /** @var \Magento\Quote\Model\Quote\Item\Option $option */
                $option = $item->getOptionByCode('simple_product');
                $simpleProduct = $option->getProduct();

                if (!$simpleProduct instanceof Product) {
                    return false;
                }

                return $this->getIsSimpleProductPreorder($simpleProduct, $qty);
            }

            if ($productTypeInstance instanceof \Magento\Bundle\Model\Product\Type) {
                /** @var \Magento\Bundle\Model\Product\Type $productTypeInstance */

                $isPreorder = false;

                foreach ($item->getChildren() as $childItem) {
                    if ($this->getQuoteItemIsPreorder($childItem, $qty)) {
                        $isPreorder = true;
                        break;
                    }
                }

                return $isPreorder;
            }
        } else {
            return $this->getIsSimpleProductPreorder($product, $qty);
        }

        return false;
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function getIsProductPreorder(Product $product)
    {
        if ($product->getIsPreorder() === null) {
            if ($product->isComposite()) {
                $result = $this->getIsCompositeProductPreorder($product);
            } else {
                $result = $this->getIsSimpleProductPreorder($product);
            }

            $product->setIsPreorder($result);
        }

        return (bool)$product->getIsPreorder();
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    protected function getIsCompositeProductPreorder(Product $product)
    {
        if (!$this->getCurrentStoreConfig('ampreorder/additional/discovercompositeoptions')
        ) {
            // We never know what options customer will select
            return false;
        }

        $typeId = $product->getTypeId();
        $typeInstance = $product->getTypeInstance();

        switch ($typeId) {
            case 'grouped':
                $result = $this->getIsGroupedProductPreorder($typeInstance, $product);
                break;

            case 'configurable':
                $result = $this->getIsConfigurableProductPreorder($typeInstance, $product);
                break;

            case 'bundle':
                $result = $this->getIsBundleProductPreorder($typeInstance, $product);
                break;

            default:
                //Cannot determinate pre-order status of product of unknown product type
                $result = false;
        }

        // Still have no implementation for bundles
        return $result;
    }

    /**
     * @param Grouped $typeInstance
     * @param Product $product
     *
     * @return bool
     */
    protected function getIsGroupedProductPreorder(Grouped $typeInstance, Product $product)
    {
        $elementaryProducts = $typeInstance->getAssociatedProducts($product);

        if (count($elementaryProducts) == 0) {
            return false;
        }

        $result = true; // for a while

        foreach ($elementaryProducts as $elementary) {
            if (!$this->getIsSimpleProductPreorder($elementary)) {
                $result = false;
                break;
            }
        }

        return $result;
    }

    /**
     * @param Configurable $typeInstance
     * @param Product $product
     *
     * @return bool
     */
    protected function getIsConfigurableProductPreorder(Configurable $typeInstance, Product $product)
    {
        $elementaryProducts = $typeInstance->getUsedProducts($product);

        if (count($elementaryProducts) == 0) {
            return false;
        }

        $result = true; // for a while

        foreach ($elementaryProducts as $elementary) {
            /** @var Product $elementary */
            if (!$this->getIsSimpleProductPreorder($elementary)) {
                $result = false;
                break;
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Bundle\Model\Product\Type $typeInstance
     * @param Product $product
     *
     * @return bool
     */
    protected function getIsBundleProductPreorder(
        \Magento\Bundle\Model\Product\Type $typeInstance,
        Product $product
    ) {
        $optionIds = $optionSelectionCounts = $optionPreorder = [];
        $options = $typeInstance->getOptionsCollection($product);

        foreach ($options as $option) {
            /** @var \Magento\Bundle\Model\Option $option */
            if (!$option->getRequired()) {
                continue;
            }

            $id = $option->getId();
            $optionIds[] = $id;
            $optionSelectionCounts[$id] = 0; // for a while
            $optionPreorder[$id] = true; // for a while
            $firstOption[$id] = true;
        }

        if (!$optionIds) {
            return false;
        }

        $selections = $typeInstance->getSelectionsCollection($optionIds, $product);
        $products = $this->getProductCollectionBySelectionsCollection($selections);

        foreach ($selections as $selection) {
            /** @var \Magento\Bundle\Model\Selection $selection */

            /** @var Product $product */
            $product = $products->getItemById($selection->getProductId());

            $isPreorder = $this->getIsSimpleProductPreorder($product);
            $optionId = $selection->getOptionId();
            $optionSelectionCounts[$optionId]++;
            $isFirstOption = isset($firstOption[$optionId]) && $firstOption[$optionId];

            if (!$isPreorder
                && (!isset($firstOption[$optionId]) || $isFirstOption)
            ) {
                $optionPreorder[$optionId] = false;
            }

            if ($isFirstOption) {
                $firstOption[$optionId] = false;
            }
        }

        $result = false; // for a while

        foreach ($optionPreorder as $id => $isPreorder) {
            if ($isPreorder && $optionSelectionCounts[$id] > 0) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * @param $selections
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function getProductCollectionBySelectionsCollection($selections)
    {
        $productIds = [];

        foreach ($selections as $selection) {
            /** @var \Magento\Bundle\Model\Selection $selection */
            $productIds[] = $selection->getProductId();
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('entity_id', ['in', $productIds]);

        return $collection;
    }

    /**
     * @param Product $product
     * @param int $requiredQty
     *
     * @return bool
     */
    protected function getIsSimpleProductPreorder(Product $product, $requiredQty = 1)
    {
        $result = false;

        /** @var \Magento\CatalogInventory\Model\Stock\Item $stockStatus */
        $stockItem = $this->stockRegistry->getStockItem($product->getId());

        if ($stockItem) {
            $isPreorder = $stockItem->getBackorders() == self::BACKORDERS_PREORDER_OPTION;
            $qtyStock = $this->inventory->getQty(
                $product->getData('sku'),
                $this->storeManager->getWebsite()->getCode()
            );

            if ($this->isOrderProcessing) {
                $qtyStock += $this->productQty->getPlacedQty($product->getId());
            }

            if ($this->allowEmpty()) {
                $disabledByQty = $this->disableForPositiveQty() && $qtyStock > 0
                    && ($qtyStock >= $requiredQty || $this->isOrderProcessing);
            } else {
                $disabledByQty = $qtyStock < 1;
            }

            $result = $isPreorder && !$disabledByQty;
        }

        return $result;
    }

    /**
     * @param $incrementId
     *
     * @return bool
     */
    public function getOrderIsPreorderFlagByIncrementId($incrementId)
    {
        // finally convert back to string to optimize SQL query
        $incrementId = '' . (int)$incrementId;

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderFactory->create();
        $order->load($incrementId, 'increment_id');

        if (!$order->getId()) {
            return false;
        }

        return $this->getOrderIsPreorderFlag($order);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @return bool
     */
    public function getOrderIsPreorderFlag(\Magento\Sales\Model\Order $order)
    {
        if ($order == null) {
            //Preorder: Cannot load preorder flag for null order. Processing as a regular order.
            return false;
        }

        /** @var \Amasty\Preorder\Model\ResourceModel\OrderPreorder $orderPreorderResource */
        $orderPreorderResource = $this->orderPreorderFactory->create()->getResource();

        return $orderPreorderResource->getOrderIsPreorderFlag($order->getId());
    }

    /**
     * @param $orderId
     *
     * @return string
     */
    public function getOrderPreorderWarning($orderId)
    {
        /** @var \Amasty\Preorder\Model\ResourceModel\OrderPreorder $orderPreorderResource */
        $orderPreorderResource = $this->orderPreorderFactory->create()->getResource();
        $warning = $orderPreorderResource->getWarningByOrderId($orderId);

        if ($warning == null) {
            $warning = $this->getCurrentStoreConfig('ampreorder/general/orderpreorderwarning');
        }

        return $warning;
    }

    /**
     * @param $itemId
     *
     * @return bool
     */
    public function getOrderItemIsPreorderFlag($itemId)
    {
        /** @var \Amasty\Preorder\Model\ResourceModel\OrderItemPreorder\Collection $orderItemPreorderCollection */
        $orderItemPreorderCollection = $this->orderItemPreorderFactory->create()->getCollection()
            ->addFieldToFilter('order_item_id', $itemId)
            ->addFieldToSelect('is_preorder');
        $orderItemPreorderCollection->getSelect()->limit(1);
        $orderItemPreorder = $orderItemPreorderCollection->getFirstItem();

        return (bool)$orderItemPreorder->getIsPreorder();
    }

    /**
     * @param $quoteItem
     *
     * @return null|string|string[]
     */
    public function getQuoteItemPreorderNote($quoteItem)
    {
        $note = '';

        if (!$quoteItem) {
            return $note;
        }

        $product = $quoteItem->getProduct();

        if ($quoteItem->getProductType() == 'configurable') {
            $option = $quoteItem->getOptionByCode('simple_product');
            $product = $option->getProduct();
        }

        if ($this->getIsProductPreorder($product)) {
            $note = $this->getProductPreorderNote($product);
        }

        return $note;
    }

    /**
     * @param Product $product
     *
     * @return null|string|string[]
     */
    public function getProductPreorderNote(Product $product)
    {
        $template = $product->getData('amasty_preorder_note');

        if (!$product->hasData('amasty_preorder_note')) {
            $template = $product->getResource()
                ->getAttributeRawValue($product->getId(), 'amasty_preorder_note', $product->getStoreId());
        }

        if (!$template) {
            $template = $this->getCurrentStoreConfig('ampreorder/general/defaultpreordernote');
        }

        $template = $this->stripTags($template, self::ALLOWED_TAGS);
        $note = $this->templater->process($template, $product);

        if (is_array($note)) {
            $note = implode($note);
        }

        return $note;
    }

    /**
     * @return string
     */
    public function getPreorderNotePosition()
    {
        return $this->getCurrentStoreConfig('ampreorder/general/note_position');
    }

    /**
     * Wrapper for standard strip_tags() function with extra functionality for html entities
     *
     * @param string $data
     * @param string|null $allowableTags
     * @param bool $allowHtmlEntities
     * @return string
     */
    public function stripTags($data, $allowableTags = null, $allowHtmlEntities = false)
    {
        return $this->filterManager->stripTags(
            $data,
            ['allowableTags' => $allowableTags, 'escape' => $allowHtmlEntities]
        );
    }

    /**
     * @param Product $product
     *
     * @return null|string|string[]
     */
    public function getProductPreorderCartLabel(Product $product)
    {
        $template = $product->getData('amasty_preorder_cart_label');

        if (!$product->hasData('amasty_preorder_cart_label')) {
            $template = $product->getResource()
                ->getAttributeRawValue($product->getId(), 'amasty_preorder_cart_label', $product->getStoreId());
        }

        if (!$template) {
            $template = $this->getDefaultPreorderCartLabel();
        }

        $template = $this->stripTags($template, self::ALLOWED_TAGS);
        $note = $this->templater->process($template, $product);

        if (is_array($note)) {
            $note = implode($note);
        }

        return $note;
    }

    /**
     * @return string
     */
    public function getDefaultPreorderCartLabel()
    {
        return $this->getCurrentStoreConfig('ampreorder/general/addtocartbuttontext');
    }

    /**
     * @return bool
     */
    public function preordersEnabled()
    {
        return $this->getCurrentStoreConfig('ampreorder/functional/enabled');
    }

    /**
     * @return bool
     */
    public function disableForPositiveQty()
    {
        return (bool)$this->getCurrentStoreConfig('ampreorder/functional/disableforpositiveqty');
    }

    /**
     * @return bool
     */
    public function allowEmpty()
    {
        return (bool)$this->getCurrentStoreConfig('ampreorder/functional/allowemptyqty');
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    protected function getCurrentStoreConfig($path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param bool $fromDatabase
     *
     * @return null|string|\string[]
     */
    public function getOrderItemPreorderNote(\Magento\Sales\Model\Order\Item $orderItem, $fromDatabase = true)
    {
        $note = '';

        if ($fromDatabase) {
            $note = $this->getNoteFromDatabase($orderItem);
        }

        if (empty($note)) {
            $product = $orderItem->getProduct();

            if ($orderItem->getProductType() == 'configurable') {
                $option = $orderItem->getOptionByCode('simple_product');

                if ($option) {
                    $product = $option->getProduct();
                }
            }
            if ($product) {
                $note = $this->getProductPreorderNote($product);
            }
        }

        return $note;
    }

    /**
     * Retrieve preorder note for ordered item from database
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     *
     * @return string
     */
    public function getNoteFromDatabase($orderItem)
    {
        $note = '';
        /** @var \Amasty\Preorder\Model\OrderItemPreorder $orderItemPreorder */
        $orderItemPreorder = $this->orderItemPreorderFactory->create();
        $orderItemPreorder->loadByItemId($orderItem->getItemId());

        if ($orderItemPreorder->getId() && $orderItemPreorder->getPreorderNote()) {
            $note = $orderItemPreorder->getPreorderNote();
        }

        return $note;
    }

    /**
     * @return mixed
     */
    public function isPreOrderNoteShow()
    {
        return $this->getCurrentStoreConfig('ampreorder/general/show_preorder_note');
    }

    /**
     * @return bool
     */
    public function isWarningInEmail()
    {
        return (bool)$this->getCurrentStoreConfig('ampreorder/additional/addwarningtoemail');
    }

    /**
     * @return mixed
     */
    public function getCartMessage()
    {
        return $this->getCurrentStoreConfig('ampreorder/general/cart_message');
    }

    /**
     * @return string
     */
    public function getBelowZeroMessage()
    {
        return $this->getCurrentStoreConfig('ampreorder/general/below_zero_message');
    }
}
