<?php
namespace TemplateMonster\SiteMaintenance\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_PATH_ACTIVE = 'site_maintenance/general/active';
    const CONFIG_PATH_TITLE = 'site_maintenance/general/title';
    const CONFIG_PATH_WHITELIST = 'site_maintenance/general/whitelist';
    const CONFIG_PATH_LOGO = 'site_maintenance/general/logo';
    const CONFIG_PATH_BACKGROUND_TYPE = 'site_maintenance/general/background_type';
    const CONFIG_PATH_BACKGROUND_COLOR = 'site_maintenance/general/background_color';
    const CONFIG_PATH_BACKGROUND_IMAGE = 'site_maintenance/general/background_image';
    const CONFIG_PATH_BACKGROUND_REPEAT = 'site_maintenance/general/background_repeat';
    const CONFIG_PATH_BACKGROUND_POSITION = 'site_maintenance/general/background_position';
    const CONFIG_PATH_BACKGROUND_SIZE = 'site_maintenance/general/background_size';
    const CONFIG_PATH_BACKGROUND_ATTACHMENT = 'site_maintenance/general/background_attachment';
    const CONFIG_PATH_PAGE_DESCRIPTION = 'site_maintenance/general/page_description';
    const CONFIG_PATH_TIMER_ACTIVE = 'site_maintenance/timer/active';
    const CONFIG_PATH_TIMER_TEXT = 'site_maintenance/timer/timer_text';
    const CONFIG_PATH_TIMER_DATETIME = 'site_maintenance/timer/datetime';
    const CONFIG_PATH_TIMER_FORMAT = 'site_maintenance/timer/format';
    const CONFIG_PATH_FORM_ACTIVE = 'site_maintenance/form/active';
    const CONFIG_PATH_FORM_TITLE = 'site_maintenance/form/title';
    const CONFIG_PATH_FORM_TEXT = 'site_maintenance/form/text';

    protected $_scopeConfig;
    protected $_storeManager;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
    }

    protected function getConfigValue($path, $scope)
    {
        return $this->_scopeConfig->getValue($path, $scope);
    }

    public function isModuleActive()
    {
        return $this->getConfigValue(self::CONFIG_PATH_ACTIVE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getWhitelist()
    {
        $whitelist = str_replace(
            " ",
            "",
            $this->getConfigValue(self::CONFIG_PATH_WHITELIST, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
        );
        $whitelist = str_replace("\r\n", "", $whitelist);
        $whitelist = str_replace("\n", "", $whitelist);

        return explode(',', $whitelist);
    }

    public function getTitle()
    {
        return $this->getConfigValue(self::CONFIG_PATH_TITLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getLogo()
    {
        $image = false;
        if ($file = $this->getConfigValue(self::CONFIG_PATH_LOGO, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $image = $this->_storeManager->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'logo/' . $file;
        }
        return $image;
    }

    public function getPageDescription()
    {
        return $this->getConfigValue(self::CONFIG_PATH_PAGE_DESCRIPTION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getBackgroundType()
    {
        $value = $this->getConfigValue(self::CONFIG_PATH_BACKGROUND_TYPE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $result = 'color';
        switch ($value) {
            case 0:
                $result = 'color';
                break;
            case 1:
                $result = 'image';
                break;
        }
        return $result;
    }

    public function getBackgroundColor()
    {
        return $this->getConfigValue(self::CONFIG_PATH_BACKGROUND_COLOR, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getBackgroundImage()
    {
        $image = false;
        if ($file = $this->getConfigValue(self::CONFIG_PATH_BACKGROUND_IMAGE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $image = $this->_storeManager->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'background_image/' . $file;
        }
        return $image;
    }

    public function getBackgroundRepeat()
    {
        $value = $this->getConfigValue(self::CONFIG_PATH_BACKGROUND_REPEAT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $result = 'repeat';
        switch ($value) {
            case 0:
                $result = 'repeat';
                break;
            case 1:
                $result = 'no-repeat';
                break;
            case 2:
                $result = 'repeat-x';
                break;
            case 3:
                $result = 'repeat-y';
                break;
        }
        return $result;
    }

    public function getBackgroundPosition()
    {
        $value = $this->getConfigValue(self::CONFIG_PATH_BACKGROUND_POSITION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $result = 'left top';
        switch ($value) {
            case 0:
                $result = 'left top';
                break;
            case 1:
                $result = 'center top';
                break;
            case 2:
                $result = 'right top';
                break;
            case 3:
                $result = 'left center';
                break;
            case 4:
                $result = 'center center';
                break;
            case 5:
                $result = 'right center';
                break;
            case 6:
                $result = 'left bottom';
                break;
            case 7:
                $result = 'center bottom';
                break;
            case 8:
                $result = 'right bottom';
                break;
        }
        return $result;
    }

    public function getBackgroundSize()
    {
        $value = $this->getConfigValue(self::CONFIG_PATH_BACKGROUND_SIZE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $result = 'auto';
        switch ($value) {
            case 0:
                $result = 'auto';
                break;
            case 1:
                $result = 'contain';
                break;
            case 2:
                $result = 'cover';
                break;
        }
        return $result;
    }

    public function getBackgroundAttachment()
    {
        $value = $this->getConfigValue(self::CONFIG_PATH_BACKGROUND_ATTACHMENT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $result = 'scroll';
        switch ($value) {
            case 0:
                $result = 'scroll';
                break;
            case 1:
                $result = 'fixed';
                break;
        }
        return $result;
    }

    public function isTimerActive()
    {
        return $this->getConfigValue(self::CONFIG_PATH_TIMER_ACTIVE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getTimerText()
    {
        return $this->getConfigValue(self::CONFIG_PATH_TIMER_TEXT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getTimerDate()
    {
        return $this->getConfigValue(self::CONFIG_PATH_TIMER_DATETIME, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getTimerFormat()
    {
        return $this->getConfigValue(self::CONFIG_PATH_TIMER_FORMAT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isFormActive()
    {
        return $this->getConfigValue(self::CONFIG_PATH_FORM_ACTIVE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getFormTitle()
    {
        return $this->getConfigValue(self::CONFIG_PATH_FORM_TITLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getFormText()
    {
        return $this->getConfigValue(self::CONFIG_PATH_FORM_TEXT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getClientIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}