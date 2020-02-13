<?php

namespace TemplateMonster\SocialSharing\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Data helper.
 */
class Data extends AbstractHelper
{
    /**
     * Enabled config path.
     */
    const XML_PATH_ENABLED = 'social_sharing/general/enabled';

    /**
     * AddThis profile id config path.
     */
    const XML_PATH_PROFILE_ID = 'social_sharing/general/profile_id';

    /**
     * Icons style config path.
     */
    const XML_PATH_STYLE = 'social_sharing/look_and_feel/style';

    /**
     * Custom button image config path.
     */
    const XML_PATH_CUSTOM_BUTTON = 'social_sharing/look_and_feel/custom_button';

    /**
     * Custom buttons code config path.
     */
    const XML_PATH_CUSTOM_CODE = 'social_sharing/look_and_feel/custom_code';

    /**
     * Custom metadata config path.
     */
    const XML_PATH_CUSTOM_METADATA = 'social_sharing/custom_metadata';

    /**
     * Custom service config option.
     */
    const XML_PATH_CUSTOM_SERVICE = 'social_sharing/custom_service';

    /**
     * Exclude services config path.
     */
    const XML_PATH_EXCLUDE_SERVICES = 'social_sharing/api/exclude_services';

    /**
     * Compact menu services config path.
     */
    const XML_PATH_COMPACT_MENU_SERVICES = 'social_sharing/api/compact_menu_services';

    /**
     * Expanded menu services config path.
     */
    const XML_PATH_EXPANDED_MENU_SERVICES = 'social_sharing/api/expanded_menu_services';

    /**
     * Compact menu hover config path.
     */
    const XML_PATH_COMPACT_MENU_HOVER = 'social_sharing/api/compact_menu_hover';

    /**
     * Delay config path.
     */
    const XML_PATH_DELAY = 'social_sharing/api/delay';

    /**
     * Compact menu direction config path.
     */
    const XML_PATH_COMPACT_MENU_DIRECTION = 'social_sharing/api/compact_menu_direction';

    /**
     * New window config path.
     */
    const XML_PATH_NEW_WINDOW = 'social_sharing/api/new_window';

    /**
     * Menu language config path.
     */
    const XML_PATH_MENU_LANGUAGE = 'social_sharing/api/menu_language';

    /**
     * Offset top config path.
     */
    const XML_PATH_OFFSET_TOP = 'social_sharing/api/offset_top';

    /**
     * Offset left config path.
     */
    const XML_PATH_OFFSET_LEFT = 'social_sharing/api/offset_left';

    /**
     * Load AddThis config path.
     */
    const XML_PATH_LOAD_ADDTHIS_CSS = 'social_sharing/api/load_addthis_css';

    /**
     * Track clickbacks config path.
     */
    const XML_PATH_TRACK_CLICKBACKS = 'social_sharing/api/track_clickbacks';

    /**
     * Google analytics id config path.
     */
    const XML_PATH_GOOGLE_ANALYTICS_ID = 'social_sharing/api/google_analytics_id';

    /**
     * Check if module enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get profile id.
     *
     * @return string
     */
    public function getProfileId()
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_PROFILE_ID,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get style of the icons.
     *
     * @return string
     */
    public function getStyle()
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_STYLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get custom button image.
     *
     * @return string
     */
    public function getCustomButton()
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_CUSTOM_BUTTON,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get custom buttons code.
     *
     * @return string
     */
    public function getCustomCode()
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_CUSTOM_CODE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get custom service options.
     *
     * @return array
     */
    public function getCustomMetadata()
    {
        return array_filter($this->scopeConfig->getValue(
            self::XML_PATH_CUSTOM_METADATA,
            ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * Get custom service options.
     *
     * @return array
     */
    public function getCustomService()
    {
        return array_filter($this->scopeConfig->getValue(
            self::XML_PATH_CUSTOM_SERVICE,
            ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * Get excluded services.
     *
     * @return string
     */
    public function getExcludedServices()
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_EXCLUDE_SERVICES,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get compact menu services.
     *
     * @return string
     */
    public function getCompactMenuServices()
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_COMPACT_MENU_SERVICES,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get expanded menu services.
     *
     * @return string
     */
    public function getExpandedMenuServices()
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_EXPANDED_MENU_SERVICES,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if compact menu hover.
     *
     * @return bool
     */
    public function isCompactMenuHover()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_COMPACT_MENU_SERVICES,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get delay.
     *
     * @return int
     */
    public function getDelay()
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_DELAY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get compact menu direction.
     *
     * @return mixed
     */
    public function getCompactMenuDirection()
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_COMPACT_MENU_DIRECTION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if open in new window.
     *
     * @return bool
     */
    public function isNewWindow()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_NEW_WINDOW,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get menu language.
     *
     * @return string
     */
    public function getMenuLanguage()
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_MENU_LANGUAGE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get top offset.
     *
     * @return int
     */
    public function getOffsetTop()
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_OFFSET_TOP,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get offset left.
     *
     * @return int
     */
    public function getOffsetLeft()
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_OFFSET_LEFT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if need to load AddThis CSS.
     *
     * @return bool
     */
    public function isLoadAddthisCss()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_LOAD_ADDTHIS_CSS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if need to track clickbacks.
     *
     * @return bool
     */
    public function isTrackClickbacks()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_TRACK_CLICKBACKS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get google analytics id.
     *
     * @return string
     */
    public function getGoogleAnalyticsId()
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_GOOGLE_ANALYTICS_ID,
            ScopeInterface::SCOPE_STORE
        );
    }
}
