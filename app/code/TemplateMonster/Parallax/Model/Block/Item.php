<?php

namespace TemplateMonster\Parallax\Model\Block;

use TemplateMonster\Parallax\Api\Data\BlockItemInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Block item entity model.
 *
 * @package TemplateMonster\Parallax\Model\Block
 */
class Item extends AbstractModel implements BlockItemInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('TemplateMonster\Parallax\Model\ResourceModel\Block\Item');
    }

    /**
     * @inheritdoc
     */
    public function getBlockId()
    {
        return $this->getData(self::BLOCK_ID);
    }

    /**
     * @inheritdoc
     */
    public function setBlockId($blockId)
    {
        return $this->setData(self::BLOCK_ID, $blockId);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return (int) $this->getData(self::TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * @inheritdoc
     */
    public function getOffset()
    {
        return $this->getData(self::OFFSET);
    }

    /**
     * @inheritdoc
     */
    public function setOffset($offset)
    {
        return $this->setData(self::OFFSET, $offset);
    }

    /**
     * @inheritdoc
     */
    public function isInverse()
    {
        return (bool) $this->getData(self::IS_INVERSE);
    }

    /**
     * @inheritdoc
     */
    public function setIsInverse($isIsInverse)
    {
        return $this->setData((bool) self::IS_INVERSE, $isIsInverse);
    }

    /**
     * @inheritdoc
     */
    public function getCssClass()
    {
        return $this->getData(self::CSS_CLASS);
    }

    /**
     * @inheritdoc
     */
    public function setCssClass($cssClass)
    {
        return $this->getData(self::CSS_CLASS, $cssClass);
    }

    /**
     * @inheritdoc
     */
    public function getLayoutSpeed()
    {
        return $this->getData(self::LAYOUT_SPEED);
    }

    /**
     * @inheritdoc
     */
    public function setLayoutSpeed($layoutSpeed)
    {
        return $this->setData(self::LAYOUT_SPEED, $layoutSpeed);
    }

    /**
     * @inheritdoc
     */
    public function getSortOrder()
    {
        return $this->setData(self::SORT_ORDER);
    }

    /**
     * @inheritdoc
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * @inheritdoc
     */
    public function isFade()
    {
        return (bool) $this->getData(self::IS_FADE);
    }

    /**
     * @inheritdoc
     */
    public function setIsFade($isFade)
    {
        return $this->setData((bool) self::IS_FADE, $isFade);
    }

    /**
     * @inheritdoc
     */
    public function getText()
    {
        return $this->getData(self::TEXT);
    }

    /**
     * @inheritdoc
     */
    public function setText($text)
    {
        return $this->setData(self::TEXT, $text);
    }

    /**
     * @inheritdoc
     */
    public function getImage()
    {
        return $this->getData(self::IMAGE);
    }

    /**
     * @inheritdoc
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * @inheritdoc
     */
    public function getVideoFormat()
    {
        return (int) $this->getData(self::VIDEO_FORMAT);
    }

    /**
     * @inheritdoc
     */
    public function setVideoFormat($videoFormat)
    {
        return $this->setData(self::VIDEO_FORMAT, $videoFormat);
    }

    /**
     * @inheritdoc
     */
    public function getVideoId()
    {
        return $this->getData(self::VIDEO_ID);
    }

    /**
     * @inheritdoc
     */
    public function setVideoId($videoId)
    {
        return $this->setData(self::VIDEO_ID, $videoId);
    }

    /**
     * @inheritdoc
     */
    public function getVideoMp4()
    {
        return $this->getData(self::VIDEO_MP4);
    }

    /**
     * @inheritdoc
     */
    public function setVideoMp4($video)
    {
        return $this->setData(self::VIDEO_MP4, $video);
    }

    /**
     * @inheritdoc
     */
    public function getVideoWebM()
    {
        return $this->getData(self::VIDEO_WEBM);
    }

    /**
     * @inheritdoc
     */
    public function setVideoWebM($video)
    {
        return $this->setData(self::VIDEO_WEBM, $video);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Check is specified type.
     *
     * @param string $type
     *
     * @return bool
     */
    public function isType($type)
    {
        return $this->getType() === $type;
    }

    /**
     * Check is background image layer.
     */
    public function isBackgroundImage()
    {
        return $this->isType(self::TYPE_BACKGROUND_IMAGE);
    }

    /**
     * Check is background video layer.
     */
    public function isBackgroundVideo()
    {
        return $this->isType(self::TYPE_BACKGROUND_VIDEO);
    }

    /**
     * Check is image layer.
     */
    public function isImage()
    {
        return $this->isType(self::TYPE_IMAGE);
    }

    /**
     * Check is text layer.
     */
    public function isText()
    {
        return $this->isType(self::TYPE_TEXT);
    }

    /**
     * Check is static video.
     */
    public function isStaticVideo()
    {
        return $this->getVideoFormat() === self::VIDEO_FORMAT_STATIC;
    }

    /**
     * Check is youtube video.
     */
    public function isYoutubeVideo()
    {
        return $this->getVideoFormat() === self::VIDEO_FORMAT_YOUTUBE;
    }
}
