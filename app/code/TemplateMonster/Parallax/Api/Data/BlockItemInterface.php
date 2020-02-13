<?php

namespace TemplateMonster\Parallax\Api\Data;

/**
 * Block item api interface.
 *
 * @package TemplateMonster\Parallax\Api\Data
 */
interface BlockItemInterface
{
    const ITEM_ID = 'item_id';

    const BLOCK_ID = 'block_id';

    const NAME = 'name';

    const TYPE = 'type';

    const OFFSET = 'offset';

    const IS_INVERSE = 'is_inverse';

    const CSS_CLASS = 'css_class';

    const LAYOUT_SPEED = 'layout_speed';

    const SORT_ORDER = 'sort_order';

    const IS_FADE = 'is_fade';

    const TEXT = 'text';

    const IMAGE = 'image';

    const VIDEO_FORMAT = 'video_format';

    const VIDEO_ID = 'video_id';

    const VIDEO_MP4 = 'video_mp4';

    const VIDEO_WEBM = 'video_webm';

    const STATUS = 'status';

    const TYPE_BACKGROUND_IMAGE = 0;

    const TYPE_BACKGROUND_VIDEO = 1;

    const TYPE_IMAGE = 2;

    const TYPE_TEXT = 3;

    const VIDEO_FORMAT_STATIC = 0;

    const VIDEO_FORMAT_YOUTUBE = 1;

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
     * Get id.
     *
     * @return int
     */
    public function getBlockId();

    /**
     * Set id.
     *
     * @param int $blockId
     *
     * @return $this
     */
    public function setBlockId($blockId);

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
     * Get item type.
     *
     * @return int
     */
    public function getType();

    /**
     * Set item type.
     *
     * @param int $type
     *
     * @return $this
     */
    public function setType($type);

    /**
     * Get offset.
     *
     * @return int
     */
    public function getOffset();

    /**
     * Set offset.
     *
     * @param int $offset
     *
     * @return mixed
     */
    public function setOffset($offset);

    /**
     * Check is inverse.
     *
     * @return int
     */
    public function isInverse();

    /**
     * Set is inverse.
     *
     * @param bool $isInverse
     *
     * @return $this
     */
    public function setIsInverse($isInverse);

    /**
     * Get CSS-class.
     *
     * @return string
     */
    public function getCssClass();

    /**
     * Set CSS-class/
     *
     * @param string $cssClass
     *
     * @return $this
     */
    public function setCssClass($cssClass);

    /**
     * Get layout speed.
     *
     * @return string
     */
    public function getLayoutSpeed();

    /**
     * Set layout speed.
     *
     * @param string $layoutSpeed
     *
     * @return $this
     */
    public function setLayoutSpeed($layoutSpeed);

    /**
     * Get sort order.
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Set sort order.
     *
     * @param int $sortOrder
     *
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * Check is fade.
     *
     * @return bool
     */
    public function isFade();

    /**
     * Set is fade.
     *
     * @param bool $isFade
     *
     * @return $this
     */
    public function setIsFade($isFade);

    /**
     * Get text.
     *
     * @return string
     */
    public function getText();

    /**
     * Set text.
     *
     * @param string $text
     *
     * @return $this
     */
    public function setText($text);

    /**
     * Get image.
     *
     * @return string
     */
    public function getImage();

    /**
     * Set image.
     *
     * @param string $image
     *
     * @return $this
     */
    public function setImage($image);

    /**
     * Get video format.
     *
     * @return int
     */
    public function getVideoFormat();

    /**
     * Set video format.
     *
     * @param int $videoFormat
     *
     * @return $this
     */
    public function setVideoFormat($videoFormat);

    /**
     * Get video id.
     *
     * @return string
     */
    public function getVideoId();

    /**
     * Set video id.
     *
     * @param string $videoId
     *
     * @return $this
     */
    public function setVideoId($videoId);

    /**
     * Get mp4-video.
     *
     * @return string
     */
    public function getVideoMp4();

    /**
     * Set mp4-video.
     *
     * @param string $video
     *
     * @return $this
     */
    public function setVideoMp4($video);

    /**
     * Get webm-video.
     *
     * @return $this
     */
    public function getVideoWebM();

    /**
     * Set webm-video.
     *
     * @param string $video
     *
     * @return $this
     */
    public function setVideoWebM($video);

    /**
     * Get status.
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
