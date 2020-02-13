<?php

namespace TemplateMonster\Parallax\Block\Widget;

use TemplateMonster\Parallax\Api\BlockRepositoryInterface;
use TemplateMonster\Parallax\Model\Block;
use TemplateMonster\Parallax\Helper\Data as ParallaxHelper;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;

/**
 * Class Layers
 *
 * @package TemplateMonster\Parallax\Block
 */
class Parallax extends Template implements BlockInterface, IdentityInterface
{
    /**
     * @var ParallaxHelper
     */
    protected $_helper;

    /**
     * @var BlockRepositoryInterface
     */
    protected $_blockRepository;

    /**
     * @var string
     */
    protected $_template = 'layers.phtml';

    /**
     * @var null
     */
    private $_parallaxBlock = null;

    /**
     * Layers constructor.
     *
     * @param BlockRepositoryInterface $blockRepository
     * @param ParallaxHelper           $helper
     * @param Template\Context         $context
     * @param array                    $data
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        ParallaxHelper $helper,
        Template\Context $context,
        array $data = []
    )
    {
        $this->_blockRepository = $blockRepository;
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    public function getIdentities()
    {
        return [Block::CACHE_TAG . $this->getBlockId()];
    }

    /**
     * Get block id.
     *
     * @return int
     * @throws LocalizedException
     */
    public function getBlockId()
    {
        if (!$this->hasData('block_id')) {
            throw new LocalizedException(__('Parallax block id isn\'t specified.'));
        }
        return $this->getData('block_id');
    }

    /**
     * Get parallax block.
     *
     * @return \TemplateMonster\Parallax\Model\Block
     */
    public function getParallaxBlock()
    {
        if (null === $this->_parallaxBlock) {
            $this->_parallaxBlock = $this->_blockRepository->getById($this->getBlockId());
        }

        return $this->_parallaxBlock;
    }

    /**
     * Get image url.
     *
     * @param string $image
     *
     * @return string
     */
    public function getImageUrl($image)
    {
        return sprintf(
            '%s%s/%s',
            $this->_getMediaBaseUrl(),
            ParallaxHelper::IMAGE_DIR,
            $image
        );
    }

    /**
     * Get video url.
     *
     * @param string $video
     *
     * @return string
     */
    public function getVideoUrl($video)
    {
        return sprintf(
            '%s%s/%s',
            $this->_getMediaBaseUrl(),
            ParallaxHelper::VIDEO_DIR,
            $video
        );
    }

    /**
     * @inheritdoc
     */
    protected function _beforeToHtml()
    {
        $this->addChild('configure', 'TemplateMonster\Parallax\Block\Widget\Configure');

        return parent::_beforeToHtml();
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        return ($this->_isNeedToShow() && $this->getWidgetStatus()) ? parent::_toHtml() : '';
    }

    /**
     * Check if need to show block.
     *
     * @return bool
     */
    protected function _isNeedToShow()
    {
        if (!$this->_isModuleEnabled()) {
            return false;
        }

        if (!$this->_isParallaxBlockEnabled()) {
            return false;
        }

        return true;
    }

    /**
     * Check is module enabled.
     *
     * @return bool
     */
    protected function _isModuleEnabled()
    {
        return $this->_helper->isEnabled();
    }

    /**
     * Check is parallax block enabled.
     *
     * @return bool
     */
    protected function _isParallaxBlockEnabled()
    {
        return $this->getParallaxBlock()->isEnabled();
    }

    /**
     * @return mixed
     */
    protected function _getMediaBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(
            UrlInterface::URL_TYPE_MEDIA
        );
    }
}