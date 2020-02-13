<?php

namespace TemplateMonster\ShopByBrand\Model\Brand\Source;

use Magento\Framework\Data\OptionSourceInterface;

class IsActive implements OptionSourceInterface
{


    protected $brand;

    /**
     * IsActive constructor.
     * @param \TemplateMonster\ShopByBrand\Model\Brand $brand
     */
    public function __construct(\TemplateMonster\ShopByBrand\Model\Brand $brand)
    {
        $this->brand = $brand;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->brand->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

}