<?php

namespace TemplateMonster\GoogleMap\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Data Helper
 *
 * @package TemplateMonster\GoogleMap\Helper
 */
class Data extends AbstractHelper
{
    /**
     * Module active option.
     */
    const XML_PATH_ACTIVE = 'google_map/%s/active';

    /**
     * Map coordinates option.
     */
    const XML_PATH_COORDINATES = 'google_map/%s/coordinates';

    /**
     * Map zoom option.
     */
    const XML_PATH_ZOOM = 'google_map/%s/zoom';

    /**
     * Map type option.
     */
    const XML_PATH_MAP_TYPE = 'google_map/%s/map_type';

    /**
     * Width of map option.
     */
    const XML_PATH_WIDTH = 'google_map/%s/width';

    /**
     * Height of map option.
     */
    const XML_PATH_HEIGHT = 'google_map/%s/height';

    /**
     * Styles of map option.
     */
    const XML_PATH_STYLES = 'google_map/%s/styles';

    /**
     * Switcher for map ui option.
     */
    const XML_PATH_UI = 'google_map/%s/ui';

    /**
     * Switcher for map scrollwheel option.
     */
    const XML_PATH_SCROLLWHEEL = 'google_map/%s/scrollwheel';

    /**
     * Switcher for map draggable option.
     */
    const XML_PATH_DRAGGABLE = 'google_map/%s/draggable';

    /**
     * Markers option.
     */
    const XML_PATH_MARKERS = 'google_map/%s/markers';

    /**
     * Google API key option.
     */
    const XML_PATH_API_KEY = 'google_map/general/api_key';

    /**
     * Anchor for show option.
     */
    const XML_PATH_SHOW_ON = 'google_map/home/show_on';

    /**
     * Selector option.
     */
    const XML_PATH_SELECTOR = 'google_map/footer/selector';

    /**
     * Store manager.
     */
    protected $_storeManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\Request\Http $request,
        SerializerInterface $serializer = null
    )
    {
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
        parent::__construct($context);
    }

    public function getNamePages()
    {
        return $this->_request->getFullActionName();
    }

    /**
     * Is enabled module.
     *
     * @return bool
     */
    public function isEnabled($section)
    {
        return $this->scopeConfig->getValue(
            sprintf(self::XML_PATH_ACTIVE, $section), ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get map coordinates.
     *
     * @return string
     */
    public function getMapCoordinates($section)
    {
        return $this->scopeConfig->getValue(
            sprintf(self::XML_PATH_COORDINATES, $section), ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get map zoom.
     *
     * @return int
     */
    public function getZoom($section)
    {
        return (int) $this->scopeConfig->getValue(
            sprintf(self::XML_PATH_ZOOM, $section), ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get map type.
     *
     * @return string
     */
    public function getMapType($section)
    {
        return $this->scopeConfig->getValue(
            sprintf(self::XML_PATH_MAP_TYPE, $section), ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get map width.
     *
     * @return string
     */
    public function getMapWidth($section)
    {
        return $this->scopeConfig->getValue(
            sprintf(self::XML_PATH_WIDTH, $section), ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get map height.
     *
     * @return string
     */
    public function getMapHeight($section)
    {
        return $this->scopeConfig->getValue(
            sprintf(self::XML_PATH_HEIGHT, $section), ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get map style.
     *
     * @return string
     */
    public function getMapStyle($section)
    {
        return $this->scopeConfig->getValue(
            sprintf(self::XML_PATH_STYLES, $section), ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is enabled Google Map UI.
     *
     * @return bool
     */
    public function isEnabledUI($section)
    {
        return $this->scopeConfig->getValue(
            sprintf(self::XML_PATH_UI, $section), ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is enabled map scroll wheel.
     *
     * @return bool
     */
    public function isEnabledScrollWheel($section)
    {
        return $this->scopeConfig->getValue(
            sprintf(self::XML_PATH_SCROLLWHEEL, $section), ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is enabled map draggable.
     *
     * @return bool
     */
    public function isEnabledDraggable($section)
    {
        return $this->scopeConfig->getValue(
            sprintf(self::XML_PATH_DRAGGABLE, $section), ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get markers.
     *
     * @return string
     */
    public function getMarkers($section)
    {
        return $this->serializer->unserialize($this->scopeConfig->getValue(sprintf(self::XML_PATH_MARKERS, $section), ScopeInterface::SCOPE_STORE));
    }

    /**
     * Get Google API key.
     *
     * @return string
     */
    public function getApiKey()
    {
        return  $this->scopeConfig->getValue(self::XML_PATH_API_KEY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get anchor for map display at home page.
     *
     * @return string
     */
    public function getHomeMapAnchor()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SHOW_ON, ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get selector for map display at footer.
     *
     * @return string
     */
    public function getSelector()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SELECTOR, ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get path to Media.
     *
     * @return string
     */
    public function getMedia()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Get content for Infowindow.
     *
     * @return string
     */
    public function getContentInfowindow($content)
    {
        return preg_replace('/[\r\n]+(?![^(]*\))/', '', $content);
    }

}