<?php

namespace TemplateMonster\Megamenu\Model\Attribute\Frontend;

use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;

class Products extends AbstractFrontend
{
    public function getInputRendererClass()
    {
        return 'TemplateMonster\Megamenu\Block\Data\Form\Element\Products';
    }
}