<?php

namespace TemplateMonster\Megamenu\Block\Html\Topmenu\Block;

use Magento\Framework\View\Element\Template;

class Row extends Template
{
    protected $_template = 'html/topmenu/block/row.phtml';

    public function renderRow($row)
    {
        $this->setRow($row);

        return $this->toHtml();
    }

    public function renderColumns()
    {
        $result = '';
        $columnRenderer = $this->_layout->createBlock(
            'TemplateMonster\Megamenu\Block\Html\Topmenu\Block\Row\Column',
            ''
        );
        foreach ($this->getRow()->getColumns() as $column) {
            $result .= $columnRenderer->renderColumn($column);
        }
        return $result;
    }
}