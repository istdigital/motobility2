<?php

namespace TemplateMonster\ShopByBrand\Model;
use TemplateMonster\ShopByBrand\Api\Data\SoldBrandInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

class SoldBrand extends AbstractModel implements SoldBrandInterface, IdentityInterface
{

    const CACHE_TAG = 'sold_brand';

    /**
     * @var string
     */
    protected $_cacheTag = 'sold_brand';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'sold_brand';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('TemplateMonster\ShopByBrand\Model\ResourceModel\SoldBrand');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }


    public function getId()
    {
        return $this->getData(self::ITEM_ID);
    }

    public function getBrandId()
    {
        return $this->getData(self::BRAND_ID);
    }

    public function getName()
    {
        return $this->getData(self::NAME);
    }

    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     *
     *
     * @return string
     */
    public function getPurchasedDate()
    {
        return $this->getData(self::PURCHASED_DATE);
    }

    public function getBillName()
    {
        return $this->getData(self::BILL_NAME);
    }

    public function getShipName()
    {
        return $this->getData(self::SHIP_NAME);
    }

    public function getQty()
    {
        return $this->getData(self::QTY);
    }

    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    public function getBaseAmount()
    {
        return $this->getData(self::BASE_AMOUNT);
    }

    public function setId($id)
    {
        return $this->setData(self::ITEM_ID,$id);
    }

    public function setBrandId($brandId)
    {
        return $this->setData(self::BRAND_ID,$brandId);
    }

    public function setName($name)
    {
        return $this->setData(self::NAME,$name);
    }

    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID,$storeId);
    }

    public function setPurchasedDate($purchasedDate)
    {
        return $this->setData(self::PURCHASED_DATE,$purchasedDate);
    }

    public function setBillName($billName)
    {
        return $this->setData(self::BILL_NAME,$billName);
    }

    public function setShipName($shipName)
    {
        return $this->setData(self::SHIP_NAME,$shipName);
    }

    public function setQty($qty)
    {
        return $this->setData(self::QTY,$qty);
    }

    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT,$amount);
    }

    public function setBaseAmount($baseAmount)
    {
        return $this->setData(self::BASE_AMOUNT,$baseAmount);
    }

}