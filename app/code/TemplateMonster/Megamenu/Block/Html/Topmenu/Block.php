<?php

namespace TemplateMonster\Megamenu\Block\Html\Topmenu;

use Magento\Framework\View\Element\Template;

use TemplateMonster\Megamenu\Model\Configurator;

use TemplateMonster\Megamenu\Helper\Data;

class Block extends Template
{
    protected $_template = 'html/topmenu/block.phtml';

    protected $_node;

    protected $_configurator;

    protected $_helper;

    public function __construct(
        Template\Context $context,
        Configurator $configurator,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_configurator = $configurator;
        $this->_helper = $helper;
    }

    public function setNode($node)
    {
        $this->_node = $node;
    }

    public function getNode()
    {
        return $this->_node;
    }

    public function renderBlock()
    {
        return $this->toHtml();
    }

    public function renderRows()
    {
        $this->_configurator->init($this->_node);
        $result = '';
        $rowRenderer = $this->_layout->createBlock(
            'TemplateMonster\Megamenu\Block\Html\Topmenu\Block\Row',
            ''
        );
        foreach ($this->_configurator->getRows() as $row) {
            $result .= $rowRenderer->renderRow($row);
        }
        return $result;
    }

    public function getAdditionalCssClass()
    {
        if ($this->_helper->isVertical()) {
            return 'in-sidebar';
        }
        return '';
    }
}