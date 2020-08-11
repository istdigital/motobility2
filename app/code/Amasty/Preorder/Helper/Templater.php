<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Helper;

use Magento\Framework\App\Helper\Context;

class Templater extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    public function __construct(Context $context, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone)
    {
        $this->timezone = $timezone;
        parent::__construct($context);
    }

    public function process($template, \Magento\Catalog\Model\Product $product)
    {
        $this->product = $product;
        $result = preg_replace_callback('/\{([^\{\}]+)\}/', array($this, 'attributeReplaceCallback'), $template);
        return $result;
    }

    protected function attributeReplaceCallback($match)
    {
        $attributeCode = $match[1];
        $value = $this->product->getData($attributeCode);
        if (is_null($value)) {
            $value = $this->product->getResource()->getAttributeRawValue($this->product->getId(), $attributeCode, $this->product->getStoreId());
        }
        if (is_array($value)) {
            $value = isset($value[$attributeCode]) ? $value[$attributeCode] : null;
        }

        $attributes = $this->product->getAttributes();
        if (isset($attributes[$attributeCode])) {
            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
            $attribute = $attributes[$attributeCode];
            $frontend = $attribute->getFrontendInput();

            if ($frontend == 'select') {
                $value = $attribute->getSource()->getOptionText($value);
            } elseif ($frontend == 'date') {
                try {
                    $value = $this->timezone->formatDate($value, \IntlDateFormatter::MEDIUM , false);
                } catch (\Zend_Date_Exception $e) {
                    $value = '';
                }
            }
        }

        return ($value === false) ? '' : $value;
    }
}
