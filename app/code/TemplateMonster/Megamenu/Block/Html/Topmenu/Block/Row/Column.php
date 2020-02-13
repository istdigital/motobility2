<?php

namespace TemplateMonster\Megamenu\Block\Html\Topmenu\Block\Row;

use Magento\Framework\View\Element\Template;

class Column extends Template
{
    protected $_template = 'html/topmenu/block/row/column.phtml';

    public function renderColumn($column)
    {
        $this->setColumn($column);

        return $this->toHtml();
    }

    public function renderEntities()
    {
        $result = '';

        $entities = $this->getColumn()->getEntities();
        foreach ($entities as $entity) {
            $entityRenderer = $this->_layout->createBlock(
                'TemplateMonster\Megamenu\Block\Html\Topmenu\Block\Row\Column\\' . $entity->getRendererClass(),
                ''
            );
            $result .= $entityRenderer->renderEntity($entity);
        }
        return $result;
    }

    public function getColumnWidth()
    {
        return $this->getColumn()->getWidth();
    }

    public function getCssClass()
    {
        return $this->getColumn()->getCssClass();
    }
}