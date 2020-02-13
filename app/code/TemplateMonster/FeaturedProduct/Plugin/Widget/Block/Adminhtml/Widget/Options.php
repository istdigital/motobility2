<?php
/**
 * Created by PhpStorm.
 * User: monster
 * Date: 4/4/17
 * Time: 9:28 AM
 */

namespace TemplateMonster\FeaturedProduct\Plugin\Widget\Block\Adminhtml\Widget;

use \Magento\Widget\Block\Adminhtml\Widget\Options as WidgetOptions;

class Options
{

    public function afterToHtml(WidgetOptions $subject, $result)
    {
        $featuredProductInit = $subject->getLayout()->createBlock('Magento\Backend\Block\Template')
                         ->setTemplate('TemplateMonster_FeaturedProduct::widget/featured-product-widget.phtml')
                         ->toHtml();
        return $result . $featuredProductInit;
    }
}