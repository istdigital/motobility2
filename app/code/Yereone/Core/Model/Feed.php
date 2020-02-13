<?php
namespace Yereone\Core\Model;

class Feed extends \Magento\AdminNotification\Model\Feed
{
    /**
     * Feed URL
     *
     * @var string
     */
    const YEREONE_FEED_URL = 'www.yereone.com/notifications.rss';

    /**
     * Retrieve feed url
     *
     * @return string
     */
    public function getFeedUrl()
    {
        $httpPath = $this->_backendConfig->isSetFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://';
        if ($this->_feedUrl === null) {
            $this->_feedUrl = $httpPath . self::YEREONE_FEED_URL;
        }
        return $this->_feedUrl;
    }

    /**
     * Check feed for modification
     *
     * @return $this
     */
    public function checkUpdate()
    {
        if (!$this->_backendConfig->getValue('yereone/general/notice_enable')) {
            return $this;
        }
        return parent::checkUpdate();
    }

    /**
     * Retrieve Last update time
     *
     * @return int
     */
    public function getLastUpdate()
    {
        return $this->_cacheManager->load('yereone_notifications_lastcheck');
    }

    /**
     * Set last update time (now)
     *
     * @return $this
     */
    public function setLastUpdate()
    {
        $this->_cacheManager->save(time(), 'yereone_notifications_lastcheck');
        return $this;
    }
}
