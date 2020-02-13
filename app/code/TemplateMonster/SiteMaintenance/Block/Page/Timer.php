<?php
namespace TemplateMonster\SiteMaintenance\Block\Page;

use TemplateMonster\SiteMaintenance\Block\Page;

use Magento\Framework\View\Element\Template;

class Timer extends Page
{

    public function getText()
    {
        return $this->filterContent($this->helper->getTimerText());
    }

    public function getFormat()
    {
        return $this->helper->getTimerFormat();
    }

    private function getEndDateTimestamp()
    {
        $date = $this->helper->getTimerDate();
        return strtotime($date);;
    }

    public function getEndDate()
    {
        return date('Y/m/d H:i:s', $this->getEndDateTimestamp());
    }

    public function isActive()
    {
        return $this->helper->isTimerActive();
    }
}
