<?php

namespace TemplateMonster\ShopByBrand\Api\Data;

/**
 * Interface SoldBrandInterface
 *
 * @package TemplateMonster\ShopByBrand\Api\Data
 */
interface SoldBrandInterface
{
    const ITEM_ID = 'item_id';
    const BRAND_ID = 'brand_id';
    const NAME = 'name';
    const STORE_ID = 'store_id';
    const PURCHASED_DATE = 'purchased_date';
    const BILL_NAME = 'bill_name';
    const SHIP_NAME = 'ship_name';
    const QTY = 'qty';
    const AMOUNT = 'amount';
    const BASE_AMOUNT = 'base_amount';

    /**
     * Get id.
     *
     * @return int
     */
    public function getId();

    /**
     * Set id.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * Get brand id.
     *
     * @return int
     */
    public function getBrandId();

    /**
     * Set brand id.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setBrandId($id);

    /**
     * Get name.
     *
     * @return string
     */
    public function getName();

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);

    /**
     * Get store id.
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store id.
     *
     * @param int $storeId
     *
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get purchased date.
     *
     * @return string
     */
    public function getPurchasedDate();

    /**
     * Set purchased date.
     *
     * @param string $purchasedDate
     *
     * @return $this
     */
    public function setPurchasedDate($purchasedDate);

    /**
     * Get bill name.
     *
     * @return string
     */
    public function getBillName();

    /**
     * Set bill name.
     *
     * @param string $billName
     *
     * @return $this
     */
    public function setBillName($billName);

    /**
     * Get ship name.
     *
     * @return mixed
     */
    public function getShipName();

    /**
     * Set ship name.
     *
     * @param string $shipName
     *
     * @return $this
     */
    public function setShipName($shipName);

    /**
     * Get quantity.
     *
     * @return int
     */
    public function getQty();

    /**
     * Set quantity.
     *
     * @param int $qty
     *
     * @return $this
     */
    public function setQty($qty);

    /**
     * Get amount.
     *
     * @return float
     */
    public function getAmount();

    /**
     * Set amount.
     *
     * @param float $amount
     *
     * @return $this
     */
    public function setAmount($amount);

    /**
     * Get base amount.
     *
     * @return float
     */
    public function getBaseAmount();

    /**
     * Set base amount.
     *
     * @param float $baseAmount
     *
     * @return $this
     */
    public function setBaseAmount($baseAmount);
}