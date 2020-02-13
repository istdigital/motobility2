<?php

namespace TemplateMonster\ShopByBrand\Model\Source;

class Brand implements \Magento\Framework\Option\ArrayInterface
{

    protected $brand;

    public function __construct(\TemplateMonster\ShopByBrand\Api\Data\BrandInterfaceFactory $brand)
    {
        $this->brand = $brand;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        $brandCollection = $this->brand->create()->getResourceCollection();
        $brandCollection->addFieldToFilter('status','1');

        foreach ($brandCollection as $item) {
            $result[] = ['value' => $item->getId(), 'label' => __($item->getName())];
        }


        return $result;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];
        $brandCollection = $this->brand->create()->getResourceCollection();
        $brandCollection->addFieldToFilter('status','1');

        foreach ($brandCollection as $item) {
            $result[] = [$item->getId() => __($item->getName())];
        }
        return $result;
    }
}