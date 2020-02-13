<?php

namespace TemplateMonster\Parallax\Block\Adminhtml\Form\Element;

use Magento\Backend\Helper\Data;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use TemplateMonster\Parallax\Helper\Data as ParallaxHelper;
use Magento\Customer\Block\Adminhtml\Form\Element\File;

/**
 * Parallax video form type.
 */
class Video extends File
{
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Video constructor.
     *
     * @param UrlInterface      $urlBuilder
     * @param Factory           $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper           $escaper
     * @param Data              $adminhtmlData
     * @param Repository        $assetRepo
     * @param EncoderInterface  $urlEncoder
     * @param array             $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        Data $adminhtmlData,
        Repository $assetRepo,
        EncoderInterface $urlEncoder,
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        parent::__construct(
            $factoryElement, $factoryCollection, $escaper, $adminhtmlData,
            $assetRepo, $urlEncoder, $data
        );
    }

    /**
     * @inheritdoc
     */
    protected function _getPreviewUrl()
    {
        $mediaUrl = $this->_urlBuilder->getBaseUrl([
            '_type' => UrlInterface::URL_TYPE_MEDIA
        ]);

        return $mediaUrl . sprintf('%s/%s', ParallaxHelper::VIDEO_DIR, $this->getValue());
    }
}