<?php

namespace TemplateMonster\Megamenu\Block\Html\Topmenu\Block\Row\Column;

use Magento\Framework\View\Element\Template;
use Magento\Cms\Model\Template\FilterProvider;

class Widget extends Entity
{
    protected $_template = 'html/topmenu/block/row/column/widget.phtml';

    protected $_filterProvider;

    public function __construct(
        Template\Context $context,
        FilterProvider $filterProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_filterProvider = $filterProvider;
    }

    public function renderWidget()
    {
        return $this->_filterProvider->getBlockFilter()->filter($this->getEntity()->getValue());
    }
}