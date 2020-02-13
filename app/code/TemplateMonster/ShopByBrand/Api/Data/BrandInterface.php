<?php

namespace TemplateMonster\ShopByBrand\Api\Data;

/**
 * Interface BrandInterface
 *
 * @package TemplateMonster\ShopByBrand\Api\Data
 */
interface BrandInterface
{
    const BRAND_ID = 'brand_id';
    const NAME = 'name';
    const URL_PAGE = 'url_key';
    const PAGE_TITLE = 'title';
    const LOGO = 'logo';
    const BRAND_BANNER = 'brand_banner';
    const PRODUCT_BANNER = 'product_banner';
    const STATUS = 'status';
    const FEATURED = 'featured';
    const SHORT_DESCRIPTION = 'short_description';
    const MAIN_DESCRIPTION = 'main_description';
    const META_KEYWORDS = 'meta_keywords';
    const META_DESCRIPTION = 'meta_description';

    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId();

    /**
     * Set id.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * Get name.
     *
     * @return string
     */
    public function getName();

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);

    /**
     * Get url page.
     *
     * @return string
     */
    public function getUrlPage();

    /**
     * Set url page.
     *
     * @param string $urlPage
     *
     * @return $this
     */
    public function setUrlPage($urlPage);

    /**
     * Get page title.
     *
     * @return string
     */
    public function getPageTitle();

    /**
     * Set page title.
     *
     * @param string $pageTitle
     *
     * @return $this
     */
    public function setPageTitle($pageTitle);

    /**
     * Get logo.
     *
     * @return string
     */
    public function getLogo();

    /**
     * Set logo.
     *
     * @param string $logo
     *
     * @return $this
     */
    public function setLogo($logo);

    /**
     * Get brand banner.
     *
     * @return string
     */
    public function getBrandBanner();

    /**
     * Set brand banner.
     *
     * @param string $brandBanner
     *
     * @return $this
     */
    public function setBrandBanner($brandBanner);

    /**
     * Get product banner.
     *
     * @return string
     */
    public function getProductBanner();

    /**
     * Set product banner.
     *
     * @param string $productBanner
     *
     * @return $this
     */
    public function setProductBanner($productBanner);

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus();

    /**
     * Set status.
     *
     * @param int $status
     *
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get featured products.
     *
     * @return mixed
     */
    public function getFeatured();

    /**
     * Set featured.
     *
     * @param mixed $featured
     *
     * @return $this
     */
    public function setFeatured($featured);

    /**
     * Get short description.
     *
     * @return string
     */
    public function getShortDescription();

    /**
     * Set short description.
     *
     * @param string $shortDescription
     *
     * @return $this
     */
    public function setShortDescription($shortDescription);

    /**
     * Get main description.
     *
     * @return string
     */
    public function getMainDescription();

    /**
     * Set main description.
     *
     * @param string $mainDescription
     *
     * @return $this
     */
    public function setMainDescription($mainDescription);

    /**
     * Get meta keywords.
     *
     * @return string
     */
    public function getMetaKeywords();

    /**
     * Set meta keywords.
     *
     * @param string $metaKeywords
     *
     * @return $this
     */
    public function setMetaKeywords($metaKeywords);

    /**
     * Get meta description.
     *
     * @return string
     */
    public function getMetaDescription();

    /**
     * Set meta description.
     *
     * @param string $metaDescription
     *
     * @return $this
     */
    public function setMetaDescription($metaDescription);

    /**
     * Check is brand published.
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Check is brand unpublished.
     *
     * @return bool
     */
    public function isDisabled();
}