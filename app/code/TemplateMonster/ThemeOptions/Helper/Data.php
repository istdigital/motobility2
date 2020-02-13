<?php

namespace TemplateMonster\ThemeOptions\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Cms\Helper\Page as PageHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Data Helper
 *
 * @package TemplateMonster\ThemeOptions\Helper
 */
class Data extends AbstractHelper
{
    /**
     * Media directory
     */
    const MEDIA_DIR = 'theme_options';

    /**
     * Module prefix
     */
    const XML_MODULE_PREFIX = 'theme_options';

    /**
     * Color settings section
     */
    const XML_PATH_COLOR_SETTING_GROUP = 'theme_options/color_settings';

    /**
     * Logo config option.
     */
    const XML_PATH_LOGO = 'theme_options/general/logo';

    /**
     * Logo width config option.
     */
    const XML_PATH_LOGO_WIDTH = 'theme_options/general/logo_width';

    /**
     * Logo height config option.
     */
    const XML_PATH_LOGO_HEIGHT = 'theme_options/general/logo_height';

    /**
     * Logo alt config option.
     */
    const XML_PATH_LOGO_ALT = 'theme_options/general/logo_alt';

    /**
     * Welcome text config option.
     */
    const XML_PATH_WELCOME_TEXT = 'theme_options/general/welcome_text';

    /**
     * Favicon config option.
     */
    const XML_PATH_FAVICON = 'theme_options/general/favicon';

    /**
     * Site title config option.
     */
    const XML_PATH_SITE_TITLE = 'theme_options/general/site_title';

    /**
     * Description config option.
     */
    const XML_PATH_DESCRIPTION = 'theme_options/general/description';

    /**
     * Keywords config option.
     */
    const XML_PATH_KEYWORDS = 'theme_options/general/keywords';

    /**
     * Keywords config option.
     */
    const XML_PATH_COPYRIGHT = 'theme_options/general/copyright';

    /**
     * Body background color config option.
     */
    const XML_PATH_COLOR_SCHEME = 'theme_options/general/color_scheme';

    /**
     * Primary color config option.
     */
    const XML_PATH_PRIMARY_COLOR = 'theme_options/color_settings/primary_color';

    /**
     * Secondary color config option.
     */
    const XML_PATH_SECONDARY_COLOR = 'theme_options/color_settings/secondary_color';

    /**
     * Body background color config option.
     */
    const XML_PATH_BODY_BACKGROUND_COLOR = 'theme_options/color_settings/body_background_color';

    /**
     * Body background image active option.
     */
    const XML_PATH_BODY_BACKGROUND_IMAGE_ACTIVE = 'theme_options/color_settings/body_background_image_active';

    /**
     * Body background image option.
     */
    const XML_PATH_BODY_BACKGROUND_IMAGE = 'theme_options/color_settings/body_background_image';

    /**
     * Body background image position option.
     */
    const XML_PATH_BODY_BACKGROUND_IMAGE_POSITION = 'theme_options/color_settings/body_background_image_position';

    /**
     * Body background image repeat option.
     */
    const XML_PATH_BODY_BACKGROUND_IMAGE_REPEAT = 'theme_options/color_settings/body_background_image_repeat';

    /**
     * Body background image size option.
     */
    const XML_PATH_BODY_BACKGROUND_IMAGE_SIZE = 'theme_options/color_settings/body_background_image_size';

    /**
     * Body background image attachment option.
     */
    const XML_PATH_BODY_BACKGROUND_IMAGE_ATTACHMENT = 'theme_options/color_settings/body_background_image_attachment';

    /**
     * Header background color config option.
     */
    const XML_PATH_HEADER_BACKGROUND_COLOR = 'theme_options/color_settings/header_background_color';

    /**
     * Footer background color config option.
     */
    const XML_PATH_FOOTER_BACKGROUND_COLOR = 'theme_options/color_settings/footer_background_color';

    /**
     * Footer background image active option.
     */
    const XML_PATH_FOOTER_BACKGROUND_IMAGE_ACTIVE = 'theme_options/color_settings/footer_background_image_active';

    /**
     * Footer background image config option.
     */
    const XML_PATH_FOOTER_BACKGROUND_IMAGE = 'theme_options/color_settings/footer_background_image';

    /**
     * Footer background image position option.
     */
    const XML_PATH_FOOTER_BACKGROUND_IMAGE_POSITION = 'theme_options/color_settings/footer_background_image_position';

    /**
     * Footer background image repeat option.
     */
    const XML_PATH_FOOTER_BACKGROUND_IMAGE_REPEAT = 'theme_options/color_settings/footer_background_image_repeat';

    /**
     * Footer background image size option.
     */
    const XML_PATH_FOOTER_BACKGROUND_IMAGE_SIZE = 'theme_options/color_settings/footer_background_image_size';

    /**
     * Footer background image attachment option.
     */
    const XML_PATH_FOOTER_BACKGROUND_IMAGE_ATTACHMENT = 'theme_options/color_settings/footer_background_image_attachment';

    /**
     * Primary font color option.
     */
    const XML_PATH_PRIMARY_FONT_COLOR = 'theme_options/color_settings/primary_font_color';

    /**
     * Primary link color option.
     */
    const XML_PATH_PRIMARY_LINK_COLOR = 'theme_options/color_settings/primary_link_color';

    /**
     * Primary title color option.
     */
    const XML_PATH_PRIMARY_TITLE_COLOR = 'theme_options/color_settings/primary_title_color';

    /**
     * Primary font family option.
     */
    const XML_PATH_PRIMARY_FONT_FAMILY = 'theme_options/typography/primary_font_family';

    /**
     * Secondary font family option.
     */
    const XML_PATH_SECONDARY_FONT_FAMILY = 'theme_options/typography/secondary_font_family';

    /**
     * Primary font size option.
     */
    const XML_PATH_PRIMARY_FONT_SIZE = 'theme_options/typography/primary_font_size';

    /**
     * Primary line height option.
     */
    const XML_PATH_PRIMARY_LINE_HEIGHT = 'theme_options/typography/primary_line_height';

    /**
     * Primary link color option.
     */
    const XML_PATH_STICKY_MENU = 'theme_options/header/general/sticky_menu';

    /**
     *  Top links position option.
     */
    const XML_PATH_TOP_LINKS_POSITION = 'theme_options/header/general/top_links_position';

    /**
     *  Currency position option.
     */
    const XML_PATH_CURRENCY_POSITION = 'theme_options/header/general/currency_position';

    /**
     *  Top links position option.
     */
    const XML_PATH_LANGUAGE_POSITION = 'theme_options/header/general/language_position';

    /**
     *  Show social link option.
     */
    const XML_PATH_SHOW_SOCIAL_ICONS = 'theme_options/%s/social/show_social';

    /**
     *  Image social icon option.
     */
    const XML_PATH_IMAGE_SOCIAL_ICONS = 'theme_options/%s/social/image_icon';

    /**
     *  Font social icon option.
     */
    const XML_PATH_FONT_SOCIAL_ICONS = 'theme_options/%s/social/font_icon';

    /**
     *  Social icons position option.
     */
    const XML_PATH_SOCIAL_ICONS_POSITION = 'theme_options/%s/social/social_position';

    /**
     * Wishlist option.
     */
    const XML_PATH_WISHLIST = 'theme_options/sidebar/wishlist';

    /**
     * Compare option.
     */
    const XML_PATH_COMPARE = 'theme_options/sidebar/compare';

    /**
     * Recently Viewed products option.
     */
    const XML_PATH_RECENTLY_VIEWED = 'theme_options/sidebar/recently_viewed';

    /**
     * Recently Compared products option.
     */
    const XML_PATH_RECENTLY_COMPARED = 'theme_options/sidebar/recently_compared';

    /**
     * My Orders option.
     */
    const XML_PATH_ORDERS = 'theme_options/sidebar/orders';

    /**
     * Category page path config option.
     */
    const XML_PATH_CATEGORY_PAGE = 'theme_options/category_page';

    /**
     * Column number on category page option.
     */
    const XML_PATH_CATEGORY_COLUMNS_NUM = 'grid_view/columns_number';

    /**
     * Hover type option.
     */
    const XML_PATH_HOVER_TYPE = 'grid_view/hover_type';

    /**
     * Thumb width option.
     */
    const XML_PATH_THUMB_WIDTH = 'grid_view/thumb_width';

    /**
     * Thumb height option.
     */
    const XML_PATH_THUMB_HEIGHT = 'grid_view/thumb_height';

    /**
     * Thumb height option.
     */
    const XML_PATH_SLIDES_COUNT = 'grid_view/slides_count';

    /**
     * Image width on category page option.
     */
    const XML_PATH_CATEGORY_THUMBNAIL_WIDTH = '%s_view/image_width';

    /**
     * Image height on category page option.
     */
    const XML_PATH_CATEGORY_THUMBNAIL_HEIGHT = '%s_view/image_height';

    /**
     * Image aspect ratio on category page option.
     */
    const XML_PATH_CATEGORY_THUMBNAIL_RATIO = '%s_view/image_aspect_ratio';

    /**
     * Show swatches on category page option.
     */
    const XML_PATH_CATEGORY_SHOW_SWATCHES = '%s_view/show_swatches';

    /**
     * Show compare button on category page option.
     */
    const XML_PATH_CATEGORY_SHOW_COMPARE = '%s_view/show_compare';

    /**
     * Show wishlist button on category page option.
     */
    const XML_PATH_CATEGORY_SHOW_WISHLIST = '%s_view/show_wishlist';

    /**
     * Show reviews on category page option.
     */
    const XML_PATH_CATEGORY_SHOW_REVIEWS = '%s_view/show_reviews';

    /**
     * Show short description on category page option.
     */
    const XML_PATH_CATEGORY_SHOW_DESC = '%s_view/show_desc';

    /**
     * Product page path config option.
     */
    const XML_PATH_PRODUCT_PAGE = 'theme_options/product_page';

    /**
     * Show sku on product page option.
     */
    const XML_PATH_PRODUCT_SHOW_SKU = 'general/show_sku';

    /**
     * Show stock status on product page option.
     */
    const XML_PATH_PRODUCT_SHOW_STOCK = 'general/show_stock';

    /**
     * Show compare button on product page option.
     */
    const XML_PATH_PRODUCT_SHOW_COMPARE = 'general/show_compare';

    /**
     * Show wishlist button on product page option.
     */
    const XML_PATH_PRODUCT_SHOW_WISHLIST = 'general/show_wishlist';

    /**
     * Show reviews on product page option.
     */
    const XML_PATH_PRODUCT_SHOW_REVIEWS = 'general/show_reviews';

    /**
     * Show mailto button on product page option.
     */
    const XML_PATH_PRODUCT_SHOW_EMAIL_FRIEND = 'general/show_email_to_friend';

    /**
     * Show related products option.
     */
    const XML_PATH_PRODUCT_SHOW_RELATED = 'general/show_related';

    /**
     * Show checkbox on related product option.
     */
    const XML_PATH_PRODUCT_SHOW_RELATED_CHECKBOX = 'general/show_related_checkbox';

    /**
     * Show related products count option.
     */
    const XML_PATH_PRODUCT_RELATED_COUNT = 'general/related_limit';

    /**
     * Product detail upsell image height config option.
     */
    const XML_PATH_PRODUCT_DETAIL_RELATED_IMAGE_HEIGHT = 'general/related_image_height';

    /**
     * Product detail upsell image width config option.
     */
    const XML_PATH_PRODUCT_DETAIL_RELATED_IMAGE_WIDTH = 'general/related_image_width';

    /**
     * Product detail show upsell config option.
     */
    const XML_PATH_PRODUCT_DETAIL_SHOW_UPSELL = 'general/show_upsell';

    /**
     * Product detail upsell limit config option.
     */
    const XML_PATH_PRODUCT_DETAIL_UPSELL_LIMIT = 'general/upsell_limit';

    /**
     * Product detail upsell image height config option.
     */
    const XML_PATH_PRODUCT_DETAIL_UPSELL_IMAGE_HEIGHT = 'general/upsell_image_height';

    /**
     * Product detail upsell image width config option.
     */
    const XML_PATH_PRODUCT_DETAIL_UPSELL_IMAGE_WIDTH = 'general/upsell_image_width';

    /**
     * Show short description on product page option.
     */
    const XML_PATH_PRODUCT_SHOW_SHORT_DESC = 'general/show_short_desc';

    /**
     * Show description tab on product page option.
     */
    const XML_PATH_PRODUCT_TABS_DESCRIPTION = 'tabs/description';

    /**
     * Show additional tab on product page option.
     */
    const XML_PATH_PRODUCT_TABS_ADDITIONAL = 'tabs/additional';

    /**
     * Show reviews tab on product page option.
     */
    const XML_PATH_PRODUCT_TABS_REVIEW = 'tabs/review';

    /**
     * Description tab title on product page option.
     */
    const XML_PATH_PRODUCT_TABS_DESCRIPTION_TITLE = 'tabs/description_tab_title';

    /**
     * Additional tab title on product page option.
     */
    const XML_PATH_PRODUCT_TABS_ADDITIONAL_TITLE = 'tabs/additional_tab_title';

    /**
     * Reviews tab title on product page option.
     */
    const XML_PATH_PRODUCT_TABS_REVIEW_TITLE = 'tabs/review_tab_title';

    /**
     * Gallery image width on product page option.
     */
    const XML_PATH_PRODUCT_GALLERY_IMG_WIDTH = 'gallery/image_width';

    /**
     * Gallery image height on product page option.
     */
    const XML_PATH_PRODUCT_GALLERY_IMG_HEIGHT = 'gallery/image_height';

    /**
     * Image zoom on product page option.
     */
    const XML_PATH_PRODUCT_GALLERY_IMG_ZOOM = 'gallery/image_zoom';

    /**
     * Supported view types.
     *
     * @var array
     */
    protected static $supportedViewTypes = ['grid', 'list'];

    /**
     * Supported sections for Social links.
     *
     * @var array
     */
    protected static $supportedSections = ['header', 'footer'];

    /**
     * @var ColorScheme
     */
    protected $_colorScheme;

    private $serializer;

    /**
     * Data constructor.
     *
     * @param ColorScheme $colorScheme
     * @param Context $context
     */
    public function __construct(
        ColorScheme $colorScheme,
        Context $context,
        SerializerInterface $serializer = null
    )
    {
        $this->_colorScheme = $colorScheme;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
        parent::__construct($context);
    }

    /**
     * Get imagezoom on product page.
     *
     * @return bool
     */
    public function isImageZoom()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_GALLERY_IMG_ZOOM,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Logo.
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LOGO,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Logo URL.
     *
     * @return string
     */
    public function getLogoUrl()
    {
        if ($logoSrc = $this->getLogo()) {
            $mediaUrl = $this->_urlBuilder->getBaseUrl([
                '_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ]);

            return $mediaUrl . sprintf('%s/%s', self::MEDIA_DIR, $logoSrc);
        }

        return null;
    }

    /**
     * Get Logo width.
     *
     * @return string
     */
    public function getLogoWidth()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LOGO_WIDTH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Logo height.
     *
     * @return string
     */
    public function getLogoHeight()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LOGO_HEIGHT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Logo Alt.
     *
     * @return string
     */
    public function getLogoAlt()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LOGO_ALT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Welcome text.
     *
     * @return string
     */
    public function getWelcome()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_WELCOME_TEXT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Favicon.
     *
     * @return string
     */
    public function getFavicon()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FAVICON,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Favicon URL.
     *
     * @return string
     */
    public function getFaviconUrl()
    {
        if ($faviconSrc = $this->getFavicon()) {
            $mediaUrl = $this->_urlBuilder->getBaseUrl([
                '_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ]);

            return $mediaUrl . sprintf('%s/%s', self::MEDIA_DIR, $faviconSrc);
        }

        return null;
    }

    /**
     * Get Site title.
     *
     * @return string
     */
    public function getSiteTitle()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SITE_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get default description.
     *
     * @return string
     */
    public function getDefaultDescription()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DESCRIPTION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get default keywords.
     *
     * @return string
     */
    public function getDefaultKeywords()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_KEYWORDS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get copyright.
     *
     * @return string
     */
    public function getCopyright()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COPYRIGHT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get current color scheme.
     *
     * @param string|null $store
     *
     * @return string
     */
    public function getCurrentColorScheme($store = null)
    {
        return $this->getColorScheme($store);
    }

    /**
     * Get color scheme.
     *
     * @param string|null $website
     *
     * @return mixed
     */
    public function getColorScheme($website = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COLOR_SCHEME,
            ScopeInterface::SCOPE_WEBSITE,
            $website
        );
    }
    /**
     * Get primary color.
     *
     * @param string|null $store
     * @param string|null $scheme
     *
     * @return mixed
     */
    public function getPrimaryColor($store = null, $scheme = null)
    {
        $scheme = null === $scheme ? $this->getCurrentColorScheme($store) : $scheme;
        $path = $this->prefixPathWithColorScheme(self::XML_PATH_PRIMARY_COLOR, $scheme);

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store)
            ?: $this->_colorScheme->getDefaultValue($scheme, self::XML_PATH_PRIMARY_COLOR, $store);
    }

    /**
     * Get secondary color.
     *
     * @param string|null $store
     * @param string|null $scheme
     *
     * @return mixed
     */
    public function getSecondaryColor($store = null, $scheme = null)
    {
        $scheme = null === $scheme ? $this->getCurrentColorScheme($store) : $scheme;
        $path = $this->prefixPathWithColorScheme(self::XML_PATH_SECONDARY_COLOR, $scheme);

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store)
            ?: $this->_colorScheme->getDefaultValue($scheme, self::XML_PATH_SECONDARY_COLOR, $store);
    }

    /**
     * Get body background color.
     *
     * @param string|null $store
     * @param string|null $scheme
     *
     * @return mixed
     */
    public function getBodyBackgroundColor($store = null, $scheme = null)
    {
        $scheme = null === $scheme ? $this->getCurrentColorScheme($store) : $scheme;
        $path = $this->prefixPathWithColorScheme(self::XML_PATH_BODY_BACKGROUND_COLOR, $scheme);

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store)
            ?: $this->_colorScheme->getDefaultValue($scheme, self::XML_PATH_BODY_BACKGROUND_COLOR, $store);
    }

    /**
     * Check is enable body background image.
     *
     * @param string|null $store
     * @param string|null $scheme
     *
     * @return bool
     */
    public function isBodyBackgroundImage($store = null, $scheme = null)
    {
        $scheme = null === $scheme ? $this->getCurrentColorScheme($store) : $scheme;
        $path = $this->prefixPathWithColorScheme(self::XML_PATH_BODY_BACKGROUND_IMAGE_ACTIVE, $scheme);

        return $this->scopeConfig->isSetFlag($path, ScopeInterface::SCOPE_STORE, $store)
            ?: (bool)$this->_colorScheme->getDefaultValue($scheme, self::XML_PATH_BODY_BACKGROUND_IMAGE_ACTIVE);
    }

    /**
     * Get body background image.
     *
     * @param string|null $store
     * @param string|null $scheme
     *
     * @return string
     */
    public function getBodyBackgroundImage($store = null, $scheme = null)
    {
        $scheme = null === $scheme ? $this->getCurrentColorScheme($store) : $scheme;
        $path = $this->prefixPathWithColorScheme(self::XML_PATH_BODY_BACKGROUND_IMAGE, $scheme);

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store)
            ?: $this->_colorScheme->getDefaultValue($scheme, self::XML_PATH_BODY_BACKGROUND_IMAGE);
    }

    /**
     * Get body background image position.
     *
     * @param string|null $store
     * @param string|null $scheme
     *
     * @return string
     */
    public function getBodyBackgroundImagePosition($store = null, $scheme = null)
    {
        $scheme = null === $scheme ? $this->getCurrentColorScheme($store) : $scheme;
        $path = $this->prefixPathWithColorScheme(self::XML_PATH_BODY_BACKGROUND_IMAGE_POSITION, $scheme);

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store)
            ?: $this->_colorScheme->getDefaultValue($scheme, self::XML_PATH_BODY_BACKGROUND_IMAGE_POSITION);
    }

    /**
     * Get body background image repeat.
     *
     * @param string|null $store
     * @param string|null $scheme
     *
     * @return string
     */
    public function getBodyBackgroundImageRepeat($store = null, $scheme = null)
    {
        $scheme = null === $scheme ? $this->getCurrentColorScheme($store) : $scheme;
        $path = $this->prefixPathWithColorScheme(self::XML_PATH_BODY_BACKGROUND_IMAGE_REPEAT, $scheme);

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store)
            ?: $this->_colorScheme->getDefaultValue($scheme, self::XML_PATH_BODY_BACKGROUND_IMAGE_REPEAT);
    }

    /**
     * Get body background image size.
     *
     * @param string|null $store
     * @param string|null $scheme
     *
     * @return string
     */
    public function getBodyBackgroundImageSize($store = null, $scheme = null)
    {
        $scheme = null === $scheme ? $this->getCurrentColorScheme($store) : $scheme;
        $path = $this->prefixPathWithColorScheme(self::XML_PATH_BODY_BACKGROUND_IMAGE_SIZE, $scheme);

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store)
            ?: $this->_colorScheme->getDefaultValue($scheme, self::XML_PATH_BODY_BACKGROUND_IMAGE_SIZE);
    }

    /**
     * Get body background image attachment.
     *
     * @param string|null $store
     * @param string|null $scheme
     *
     * @return string
     */
    public function getBodyBackgroundImageAttachment($store = null, $scheme = null)
    {
        $scheme = null === $scheme ? $this->getCurrentColorScheme($store) : $scheme;
        $path = $this->prefixPathWithColorScheme(self::XML_PATH_BODY_BACKGROUND_IMAGE_ATTACHMENT, $scheme);

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store)
            ?: $this->_colorScheme->getDefaultValue($scheme, self::XML_PATH_BODY_BACKGROUND_IMAGE_ATTACHMENT);
    }

    /**
     * Get header background color.
     *
     * @param string|null $store
     * @param string|null $scheme
     *
     * @return string
     */
    public function getHeaderBackgroundColor($store = null, $scheme = null)
    {
        $scheme = null === $scheme ? $this->getCurrentColorScheme($store) : $scheme;
        $path = $this->prefixPathWithColorScheme(self::XML_PATH_HEADER_BACKGROUND_COLOR, $scheme);

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store)
            ?: $this->_colorScheme->getDefaultValue($scheme, self::XML_PATH_HEADER_BACKGROUND_COLOR);
    }

    /**
     * Get footer background color.
     *
     * @param string|null $store
     * @param string|null $scheme
     *
     * @return string
     */
    public function getFooterBackgroundColor($store = null, $scheme = null)
    {
        $scheme = null === $scheme ? $this->getCurrentColorScheme($store) : $scheme;
        $path = $this->prefixPathWithColorScheme(self::XML_PATH_FOOTER_BACKGROUND_COLOR, $scheme);

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store)
            ?: $this->_colorScheme->getDefaultValue($scheme, self::XML_PATH_FOOTER_BACKGROUND_COLOR);
    }

    /**
     * Check is enable footer background image.
     *
     * @param string|null $store
     * @param string|null $scheme
     *
     * @return bool
     */
    public function isFooterBackgroundImage($store = null, $scheme = null)
    {
        $scheme = null === $scheme ? $this->getCurrentColorScheme($store) : $scheme;
        $path = $this->prefixPathWithColorScheme(self::XML_PATH_FOOTER_BACKGROUND_IMAGE_ACTIVE, $scheme);

        return $this->scopeConfig->isSetFlag($path, ScopeInterface::SCOPE_STORE, $store)
            ?: (bool)$this->_colorScheme->getDefaultValue($scheme, self::XML_PATH_FOOTER_BACKGROUND_IMAGE_ACTIVE);
    }

    /**
     * Get footer background image.
     *
     * @param string|null $store
     * @param string|null $scheme
     *
     * @return string
     */
    public function getFooterBackgroundImage($store = null, $scheme = null)
    {
        $scheme = null === $scheme ? $this->getCurrentColorScheme($store) : $scheme;
        $path = $this->prefixPathWithColorScheme(self::XML_PATH_FOOTER_BACKGROUND_IMAGE, $scheme);

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store)
            ?: $this->_colorScheme->getDefaultValue($scheme, self::XML_PATH_FOOTER_BACKGROUND_IMAGE);
    }

    /**
     * Get footer background image position.
     *
     * @param string|null $store
     * @param string|null $scheme
     *
     * @return string
     */
    public function getFooterBackgroundImagePosition($store = null, $scheme = null)
    {
        $scheme = null === $scheme ? $this->getCurrentColorScheme($store) : $scheme;
        $path = $this->prefixPathWithColorScheme(self::XML_PATH_FOOTER_BACKGROUND_IMAGE_POSITION, $scheme);

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store)
            ?: $this->_colorScheme->getDefaultValue($scheme, self::XML_PATH_FOOTER_BACKGROUND_IMAGE_POSITION);
    }

    /**
     * Get footer background image repeat.
     *
     * @param string|null $store
     * @param string|null $scheme
     *
     * @return string
     */
    public function getFooterBackgroundImageRepeat($store = null, $scheme = null)
    {
        $scheme = null === $scheme ? $this->getCurrentColorScheme($store) : $scheme;
        $path = $this->prefixPathWithColorScheme(self::XML_PATH_FOOTER_BACKGROUND_IMAGE_REPEAT, $scheme);

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store)
            ?: $this->_colorScheme->getDefaultValue($scheme, self::XML_PATH_FOOTER_BACKGROUND_IMAGE_REPEAT);
    }

    /**
     * Get footer background image size.
     *
     * @param string|null $store
     * @param string|null $scheme
     *
     * @return string
     */
    public function getFooterBackgroundImageSize($store = null, $scheme = null)
    {
        $scheme = null === $scheme ? $this->getCurrentColorScheme($store) : $scheme;
        $path = $this->prefixPathWithColorScheme(self::XML_PATH_FOOTER_BACKGROUND_IMAGE_SIZE, $scheme);

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store)
            ?: $this->_colorScheme->getDefaultValue($scheme, self::XML_PATH_FOOTER_BACKGROUND_IMAGE_SIZE);
    }

    /**
     * Get footer background image attachment.
     *
     * @param string|null $store
     * @param string|null $scheme
     *
     * @return string
     */
    public function getFooterBackgroundImageAttachment($store = null, $scheme = null)
    {
        $scheme = null === $scheme ? $this->getCurrentColorScheme($store) : $scheme;
        $path = $this->prefixPathWithColorScheme(self::XML_PATH_FOOTER_BACKGROUND_IMAGE_ATTACHMENT, $scheme);

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store)
            ?: $this->_colorScheme->getDefaultValue($scheme, self::XML_PATH_FOOTER_BACKGROUND_IMAGE_ATTACHMENT);
    }

    /**
     * Get primary font family.
     *
     * @return string
     */
    public function getPrimaryFontFamily()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRIMARY_FONT_FAMILY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get secondary font family.
     *
     * @return string
     */
    public function getSecondaryFontFamily()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SECONDARY_FONT_FAMILY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get primary font size.
     *
     * @return string
     */
    public function getPrimaryFontSize()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRIMARY_FONT_SIZE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get primary line height.
     *
     * @return string
     */
    public function getPrimaryLineHeight()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRIMARY_LINE_HEIGHT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get primary font color.
     *
     * @param string|null $store
     * @param string|null $scheme
     *
     * @return string
     */
    public function getPrimaryFontColor($store = null, $scheme = null)
    {
        $scheme = null === $scheme ? $this->getCurrentColorScheme($store) : $scheme;
        $path = $this->prefixPathWithColorScheme(self::XML_PATH_PRIMARY_FONT_COLOR, $scheme);

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store)
            ?: $this->_colorScheme->getDefaultValue($scheme, self::XML_PATH_PRIMARY_FONT_COLOR);
    }

    /**
     * Get primary link color.
     *
     * @param string|null $store
     * @param string|null $scheme
     *
     * @return string
     */
    public function getPrimaryLinkColor($store = null, $scheme = null)
    {
        $scheme = null === $scheme ? $this->getCurrentColorScheme($store) : $scheme;
        $path = $this->prefixPathWithColorScheme(self::XML_PATH_PRIMARY_LINK_COLOR, $scheme);

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store)
            ?: $this->_colorScheme->getDefaultValue($scheme, self::XML_PATH_PRIMARY_LINK_COLOR);
    }

    /**
     * Get primary title color.
     *
     * @param string|null $store
     * @param string|null $scheme
     *
     * @return string
     */
    public function getPrimaryTitleColor($store = null, $scheme = null)
    {
        $scheme = null === $scheme ? $this->getCurrentColorScheme($store) : $scheme;
        $path = $this->prefixPathWithColorScheme(self::XML_PATH_PRIMARY_TITLE_COLOR, $scheme);

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store)
            ?: $this->_colorScheme->getDefaultValue($scheme, self::XML_PATH_PRIMARY_TITLE_COLOR);
    }

    /**
     * Is enable sticky menu.
     *
     * @return bool
     */
    public function isStickyMenu()
    {
        return (int)$this->scopeConfig->isSetFlag(
            self::XML_PATH_STICKY_MENU,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Position for Top links.
     *
     * @return string
     */
    public function getTopLinksPosition()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TOP_LINKS_POSITION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Position for Currency block.
     *
     * @return string
     */
    public function getCurrencyPosition()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CURRENCY_POSITION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Position for Language block.
     *
     * @return string
     */
    public function getLanguagePosition()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LANGUAGE_POSITION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is enable Social Icons.
     *
     * @param string $position
     *
     * @return bool
     */
    public function getShowSocialLinks($position)
    {
        return $this->scopeConfig->getValue(
            sprintf(self::XML_PATH_SHOW_SOCIAL_ICONS, $position),
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Image icons for social.
     *
     * @param string $section
     *
     * @return array
     */
    public function getImageIcon($section)
    {
        $this->_checkSection($section);

        return $this->serializer->unserialize($this->scopeConfig->getValue(
            sprintf(self::XML_PATH_IMAGE_SOCIAL_ICONS, $section), ScopeInterface::SCOPE_STORE));
    }

    /**
     * Get Font icons for social.
     *
     * @param string $section
     *
     * @return array
     */
    public function getFontIcon($section)
    {
        $this->_checkSection($section);

        return $this->serializer->unserialize($this->scopeConfig->getValue(
            sprintf(self::XML_PATH_FONT_SOCIAL_ICONS, $section), ScopeInterface::SCOPE_STORE));
    }

    /**
     * Get Social Links position.
     *
     * @param string $section
     *
     * @return string
     */
    public function getSocialPosition($section)
    {
        $this->_checkSection($section);

        return $this->scopeConfig->getValue(
            sprintf(self::XML_PATH_SOCIAL_ICONS_POSITION, $section),
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check is Wishlist block.
     *
     * @return bool
     */
    public function isWishlist()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_WISHLIST,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check is Compare block.
     *
     * @return bool
     */
    public function isCompare()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_COMPARE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check is Recently viewed products block.
     *
     * @return bool
     */
    public function isRecentlyViewed()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_RECENTLY_VIEWED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check is Recently compared products block.
     *
     * @return bool
     */
    public function isRecentlyCompared()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_RECENTLY_COMPARED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check is My Orders block.
     *
     * @return bool
     */
    public function isOrders()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ORDERS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get path home page.
     *
     * @return string
     */
    public function getPathHomePage()
    {
        return $this->scopeConfig->getValue(
            PageHelper::XML_PATH_HOME_PAGE,
            ScopeInterface::SCOPE_STORE
        );
    }

    // TODO Temporary getter
    /**
     * Check is show social links.
     *
     * @param string $position
     *
     * @return bool
     */
    public function isShowSocialLinks($position)
    {
        return $this->scopeConfig->isSetFlag(
            sprintf(self::XML_PATH_SHOW_SOCIAL_ICONS, $position),
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get category image width.
     *
     * @param string $viewType
     * @throws \InvalidArgumentException
     * @return int
     */
    public function getCategoryThumbWidth($viewType)
    {
        $this->_checkViewType($viewType);
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_PAGE . '/' . sprintf(self::XML_PATH_CATEGORY_THUMBNAIL_WIDTH, $viewType),
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get category image height.
     *
     * @param string $viewType
     * @throws \InvalidArgumentException
     * @return int
     */
    public function getCategoryThumbHeight($viewType)
    {
        $this->_checkViewType($viewType);
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_PAGE . '/' . sprintf(self::XML_PATH_CATEGORY_THUMBNAIL_HEIGHT, $viewType),
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is category image ratio.
     *
     * @param string $viewType
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function getCategoryThumbRatio($viewType)
    {
        $this->_checkViewType($viewType);
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CATEGORY_PAGE . '/' . sprintf(self::XML_PATH_CATEGORY_THUMBNAIL_RATIO, $viewType),
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get columns number.
     *
     * @return int
     */
    public function getCategoryColumnsNumber()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_PAGE . '/' . self::XML_PATH_CATEGORY_COLUMNS_NUM,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get hover type.
     *
     * @return int
     */
    public function getHoverType()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_PAGE . '/' . self::XML_PATH_HOVER_TYPE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get thumb width.
     *
     * @return int
     */
    public function getHoverTypeThumbWidth()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_PAGE . '/' . self::XML_PATH_THUMB_WIDTH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get thumb height.
     *
     * @return int
     */
    public function getHoverTypeThumbHeight()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_PAGE . '/' . self::XML_PATH_THUMB_HEIGHT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get thumb height.
     *
     * @return int
     */
    public function getSlidesCount()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_PAGE . '/' . self::XML_PATH_SLIDES_COUNT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is show swatches in category list.
     *
     * @param string $viewType
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function getCategoryShowSwatches($viewType)
    {
        $this->_checkViewType($viewType);
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CATEGORY_PAGE . '/' . sprintf(self::XML_PATH_CATEGORY_SHOW_SWATCHES, $viewType),
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is show compare button in category list.
     *
     * @param string $viewType
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function getCategoryShowCompare($viewType)
    {
        $this->_checkViewType($viewType);
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CATEGORY_PAGE . '/' . sprintf(self::XML_PATH_CATEGORY_SHOW_COMPARE, $viewType),
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is show wishlist button in category list.
     *
     * @param string $viewType
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function getCategoryShowWishlist($viewType)
    {
        $this->_checkViewType($viewType);

        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CATEGORY_PAGE . '/' . sprintf(self::XML_PATH_CATEGORY_SHOW_WISHLIST, $viewType),
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is show reviews in category list.
     *
     * @param string $viewType
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function getCategoryShowReviews($viewType)
    {
        $this->_checkViewType($viewType);
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CATEGORY_PAGE . '/' . sprintf(self::XML_PATH_CATEGORY_SHOW_REVIEWS, $viewType),
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is show short description in category list.
     *
     * @param string $viewType
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function getCategoryShowDesc($viewType)
    {
        $this->_checkViewType($viewType);
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CATEGORY_PAGE . '/' . sprintf(self::XML_PATH_CATEGORY_SHOW_DESC, $viewType),
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is show stock status on product page.
     *
     * @return bool
     */
    public function isProductShowStock()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_SHOW_STOCK,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is show sku on product page.
     *
     * @return bool
     */
    public function getProductShowSku()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_SHOW_SKU,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is show compare button on product page.
     *
     * @return bool
     */
    public function isProductShowCompare()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_SHOW_COMPARE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is show wishlist button on product page.
     *
     * @return bool
     */
    public function isProductShowWishlist()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_SHOW_WISHLIST,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is show email button on product page.
     *
     * @return bool
     */
    public function isProductShowEmailFiend()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_SHOW_EMAIL_FRIEND,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is show reviews summary on product page.
     *
     * @return bool
     */
    public function isProductShowReviews()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_SHOW_REVIEWS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is show short description on product page.
     *
     * @return string
     */
    public function getProductShowShortDesc()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_SHOW_SHORT_DESC,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is show related products.
     *
     * @return bool
     */
    public function isProductShowRelated()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_SHOW_RELATED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get related products count.
     *
     * @return int
     */
    public function getProductDetailRelatedLimit()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_RELATED_COUNT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is show checkbox in related product.
     *
     * @return bool
     */
    public function isProductShowRelatedCheckbox()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_SHOW_RELATED_CHECKBOX,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get product detail related image height.
     *
     * @return int
     */
    public function getProductDetailRelatedImageHeight()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_DETAIL_RELATED_IMAGE_HEIGHT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get product detail related image width.
     *
     * @return int
     */
    public function getProductDetailRelatedImageWidth()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_DETAIL_RELATED_IMAGE_WIDTH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check is show product detail upsell.
     *
     * @return bool
     */
    public function isShowProductDetailUpsell()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_DETAIL_SHOW_UPSELL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get product detail upsell limit.
     *
     * @return int
     */
    public function getProductDetailUpsellLimit()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_DETAIL_UPSELL_LIMIT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get product detail upsell image height.
     *
     * @return int
     */
    public function getProductDetailUpsellImageHeight()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_DETAIL_UPSELL_IMAGE_HEIGHT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get product detail upsell image width.
     *
     * @return int
     */
    public function getProductDetailUpsellImageWidth()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_DETAIL_UPSELL_IMAGE_WIDTH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get description tab on product page.
     *
     * @return bool
     */
    public function getProductTabsDesc()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_TABS_DESCRIPTION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get additional tab on product page.
     *
     * @return bool
     */
    public function getProductTabsAdditional()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_TABS_ADDITIONAL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get reviews tab on product page.
     *
     * @return bool
     */
    public function getProductTabsReview()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_TABS_REVIEW,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get description tab title on product page.
     *
     * @return string
     */
    public function getProductTabsDescTitle()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_TABS_DESCRIPTION_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get additional tab title on product page.
     *
     * @return string
     */
    public function getProductTabsAdditionalTitle()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_TABS_ADDITIONAL_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get reviews tab title on product page.
     *
     * @return string
     */
    public function getProductTabsReviewTitle()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_TABS_REVIEW_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get gallery image width on product page.
     *
     * @return string
     */
    public function getProductGalleryImgWidth()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_GALLERY_IMG_WIDTH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get gallery image height on product page.
     *
     * @return string
     */
    public function getProductGalleryImgHeight()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_PAGE . '/' . self::XML_PATH_PRODUCT_GALLERY_IMG_HEIGHT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Prefix path with color scheme virtual group.
     *
     * @param string $path
     * @param string|null $scheme
     *
     * @return string
     */
    public function prefixPathWithColorScheme($path, $scheme)
    {
        $path = explode('/', $path);
        array_splice($path, count($path) - 1, 0, [$scheme]);

        return implode('/', $path);
    }

    /**
     * Check product view type.
     *
     * @param string $type
     * @return void
     */
    protected function _checkViewType($type)
    {
        if (!in_array($type, self::$supportedViewTypes)) {
            throw new \InvalidArgumentException(sprintf('Invalid view type "%s" provided.', $type));
        }
    }

    /**
     * Check section for Social links.
     *
     * @param string $section
     * @return void
     */
    protected function _checkSection($section)
    {
        if (!in_array($section, self::$supportedSections)) {
            throw new \InvalidArgumentException(sprintf('Invalid section "%s" provided.', $section));
        }
    }

    /**
     * Get product item width.
     *
     * @return string
     */
    public function getProductItemWidth()
    {
        $columns = $this->getCategoryColumnsNumber();
        return $columns ? 100 / $columns . '%' : false;
    }
}
