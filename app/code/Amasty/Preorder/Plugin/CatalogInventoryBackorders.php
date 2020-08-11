<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */
namespace Amasty\Preorder\Plugin;

class CatalogInventoryBackorders
{
    /**
     * @return array
     */
    public function afterToOptionArray(
        \Magento\CatalogInventory\Model\Source\Backorders $subject,
        array $optionArray
    )
    {
        $optionArray[] = [
            'value' => \Amasty\Preorder\Helper\Data::BACKORDERS_PREORDER_OPTION,
            'label'=> __('Allow Pre-Orders')
        ];
        return $optionArray;
    }
}
