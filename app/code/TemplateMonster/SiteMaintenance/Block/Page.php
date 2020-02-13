<?php
namespace TemplateMonster\SiteMaintenance\Block;

use Magento\Framework\View\Element\Template;
use Magento\Cms\Model\Template\FilterProvider;

class Page extends Template
{
    public $helper;
    protected $_filterProvider;

    public function __construct(
        \TemplateMonster\SiteMaintenance\Helper\Data $helper,
        Template\Context $context,
        FilterProvider $filterProvider,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->_filterProvider = $filterProvider;

        parent::__construct($context, $data);
    }

    public function filterContent($data)
    {
        return $this->_filterProvider->getBlockFilter()->filter($data);
    }

}
