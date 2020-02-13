<?php

namespace TemplateMonster\Megamenu\Block\Html\Topmenu\Block\Row\Column;

use Magento\Framework\View\Element\Template;

class Entity extends Template
{
    protected $_template = 'html/topmenu/block/row/column/default.phtml';

    public function renderEntity($entity)
    {
        $this->setEntity($entity);

        return $this->toHtml();
    }
}