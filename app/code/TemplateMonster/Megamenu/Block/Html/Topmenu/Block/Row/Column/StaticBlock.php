<?php

namespace TemplateMonster\Megamenu\Block\Html\Topmenu\Block\Row\Column;

class StaticBlock extends Entity
{
    protected $_template = 'html/topmenu/block/row/column/static_block.phtml';

    public function renderStaticBlock()
    {
        return $this->_layout
            ->createBlock('Magento\Cms\Block\Block')
            ->setBlockId($this->getEntity()->getValue())
            ->toHtml();
    }
}