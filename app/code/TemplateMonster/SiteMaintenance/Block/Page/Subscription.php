<?php
namespace TemplateMonster\SiteMaintenance\Block\Page;

use TemplateMonster\SiteMaintenance\Block\Page;

use Magento\Framework\View\Element\Template;

class Subscription extends Page
{
    public $helper;
    protected $_filterProvider;

    public function isActive()
    {
        return $this->helper->isFormActive();
    }

    public function getFormActionUrl()
    {
        return $this->getUrl('maintenance/index/new', ['_secure' => true]);
    }

    public function getText()
    {
        return $this->filterContent($this->helper->getFormText());
    }
}
