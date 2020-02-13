<?php

namespace TemplateMonster\AjaxCatalog\Plugin\Catalog\Model;

class Config
{
    public function afterGetAttributeUsedForSortByArray(
    \Magento\Catalog\Model\Config $catalogConfig,
    $options
    ) {
    	unset($options['position']);
    	unset($options['name']);
    	unset($options['price']);

    	$options['high_to_low'] = __('High To Low');
    	$options['low_to_high'] = __('Low To High');
        $options['a_to_z'] = __('A - Z');
        $options['z_to_a'] = __('Z - A');
        return $options;

    }

}