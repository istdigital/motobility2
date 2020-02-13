<?php
namespace TemplateMonster\SiteMaintenance\Block\Page;

use TemplateMonster\SiteMaintenance\Block\Page;

use Magento\Framework\View\Element\Template;

class Content extends Page
{

    public function getPageDescription()
    {
        return $this->filterContent($this->helper->getPageDescription());
    }

}
