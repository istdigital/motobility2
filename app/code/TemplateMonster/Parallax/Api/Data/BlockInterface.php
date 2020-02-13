<?php

namespace TemplateMonster\Parallax\Api\Data;

/**
 * Block api interface.
 *
 * @package TemplateMonster\Parallax\Api\Data
 */
interface BlockInterface
{
    const BLOCK_ID = 'block_id';

    const NAME = 'name';

    const CSS_CLASS = 'css_class';

    const IS_FULL_WIDTH = 'is_full_width';

    const STATUS = 'status';

    const STATUS_DISABLED = 0;

    const STATUS_ENABLED = 1;

    const STORE_ID = 'store_id';

    /**
     * Get id.
     *
     * @return mixed
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
     * Get CSS-class.
     *
     * @return string
     */
    public function getCssClass();

    /**
     * Set CSS-class.
     *
     * @param string $cssClass
     *
     * @return $this
     */
    public function setCssClass($cssClass);

    /**
     * Check is full width.
     *
     * @return bool
     */
    public function isFullWidth();

    /**
     * Set full width.
     *
     * @param bool $fullWidth
     *
     * @return $this
     */
    public function setIsFullWidth($fullWidth);

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
}
