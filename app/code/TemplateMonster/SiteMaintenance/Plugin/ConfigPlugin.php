<?php
namespace TemplateMonster\SiteMaintenance\Plugin;

use Symfony\Component\Config\Definition\Exception\Exception;
use TemplateMonster\SiteMaintenance\Helper\Data as HelperData;

class ConfigPlugin
{
    protected $_helper;

    public function __construct(
        HelperData $helper
    ) {
        $this->_helper = $helper;
    }

    public function aroundSave(
        \Magento\Config\Model\Config $subject,
        \Closure $proceed
    ) {
        if ($subject->getSection() == "site_maintenance") {
            $groups = $subject->getGroups();
            $ip = $this->_helper->getClientIp();
            $whitelist = str_replace(" ", "", $groups['general']['fields']['whitelist']['value']);
            $whitelist = str_replace("\r\n", "", $whitelist);
            $whitelist = str_replace("\n", "", $whitelist);
            $whitelist = explode(",", str_replace(" ", "", $whitelist));
            try {
                if ($groups['general']['fields']['admin']['value'] == 1) {
                    $this->addIp($ip, $whitelist);
                } else {
                    $this->removeIp($ip, $whitelist);
                }
            } catch (Exception $e) {

            }
            $whitelist = array_diff($whitelist, ['']);
            $whitelist = implode(', ', $whitelist);
            $groups['general']['fields']['whitelist']['value'] = $whitelist;
            $subject->setGroups($groups);
        }
        return $proceed();
    }

    protected function addIp($ip, &$whitelist)
    {
        if (!in_array($ip, $whitelist)) {
            $whitelist []= $ip;
        }
    }

    protected function removeIp($ip, &$whitelist)
    {
         $whitelist = array_diff($whitelist, [$ip]);
    }
}