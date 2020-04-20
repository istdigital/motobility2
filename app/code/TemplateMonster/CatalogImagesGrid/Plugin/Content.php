<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\CatalogImagesGrid\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Json\DecoderInterface;
use Magento\Backend\Model\UrlInterface;
use TemplateMonster\FilmSlider\Block\Adminhtml\SliderItem\Widget\Render\SliderItem\Canvas\Image as ImageRender;

class Content
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $_jsonDecoder;

    /**
     * @var UrlInterface
     */
    protected $_backendUrl;


    public function __construct(
        RequestInterface $request,
        EncoderInterface $encoder,
        DecoderInterface $decoder,
        UrlInterface $backendUrl)
    {
        $this->_request = $request;
        $this->_jsonEncoder = $encoder;
        $this->_jsonDecoder = $decoder;
        $this->_backendUrl = $backendUrl;
    }

    public function afterGetFilebrowserSetupObject(\Magento\Cms\Block\Adminhtml\Wysiwyg\Images\Content $subject, $resultJson)
    {
        $resultArr = $this->_jsonDecoder->decode($resultJson);

        if (is_array($resultArr)
            && array_key_exists('targetElementId', $resultArr)
            && array_key_exists('onInsertUrl', $resultArr)
            && preg_match('/_image/i',$resultArr['targetElementId'])) {

            $resultArr['onInsertUrl'] = $this->_backendUrl->getUrl('cms/*/onInsert', ["refineurl"=>true]);
            $resultJson = $this->_jsonEncoder->encode($resultArr);
        }

        return $resultJson;
    }
}
