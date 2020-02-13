<?php

namespace TemplateMonster\Megamenu\Observer;

use Magento\Framework\Event\ObserverInterface;

class CatalogCategoryFlatLoadnodesBefore implements ObserverInterface
{
    //TODO check if possible use catalog_attributes.xml
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $select = $observer->getSelect();
        $select->columns([
            'mm_turn_on',
            'mm_image',
            'mm_label',
            'mm_css_class',
            'mm_configurator',
            'mm_show_subcategories',
            'mm_number_of_subcategories',
            'mm_view_mode'
        ]);
    }
}
