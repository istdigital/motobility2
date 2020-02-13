<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Block\Widget;

use Magento\Framework\View\Element\Template;

class FilmSlider extends Template implements \Magento\Widget\Block\BlockInterface
{

    protected $_storeManager;

    protected $_template = 'TemplateMonster_FilmSlider::widget/filmslider/default.phtml';

    public function __construct(
        Template\Context $context,
        \TemplateMonster\FilmSlider\Api\SliderRepositoryInterface $filmSliderRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManager $storeManager,
        array $data = []
    ) {
        $this->_filmSliderRepository = $filmSliderRepository;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    public function addUrl($url)
    {
        $mediaUrl =  $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl.$url;
    }

    public function includeAttr($name, $value)
    {
        $html = '';
        if ($value != null) {
            $html .= $name . '="' . $value . '" ';
        }
        return $html;
    }

    public function setBackground($bgcolor, $opacity)
    {
        $html = '';
        if (!$opacity) {
            $opacity = 1;
        }
        if ($bgcolor) {
            $html .= 'background: rgba(' . $this->hexToRgb($bgcolor)['red'] . ', ' . $this->hexToRgb($bgcolor)['green'] . ', ' . $this->hexToRgb($bgcolor)['blue'] . ', ' . $opacity . ');';
        }

        return $html;
    }

    protected function hexToRgb($color)
    {
        if ($color[0] == '#') {
            $color = substr($color, 1);
        }

        if (strlen($color) == 6) {
            list($red, $green, $blue) = array(
                $color[0] . $color[1],
                $color[2] . $color[3],
                $color[4] . $color[5]
            );
        } elseif (strlen($color) == 3) {
            list($red, $green, $blue) = array(
                $color[0]. $color[0],
                $color[1]. $color[1],
                $color[2]. $color[2]
            );
        } else {
            return false;
        }

        $red = hexdec($red);
        $green = hexdec($green);
        $blue = hexdec($blue);

        return array(
            'red' => $red,
            'green' => $green,
            'blue' => $blue
        );
    }

    public function loaderDimensions($jsonParams)
    {
        $params = json_decode($jsonParams, true);
        $loader = [];

        if ($params['aspectRatio'] == -1) {
            $loader['height'] = $params['height'];
            $loader['padding'] = 0;
            $loader['position'] = 'no-abs';
        } elseif ($params['aspectRatio'] != 0) {
            $loader['height'] = 'auto';
            $loader['padding'] = 100 / $params['aspectRatio'];
            $loader['position'] = 'abs';
        }

        return $loader;
    
    }

    public function createSlider()
    {
        try {
            $sliderId = $this->getSliderId();
            $modelRepository = $this->_filmSliderRepository->getById($sliderId);
        } catch (\Exception $e) {
            echo __('Can not load slider');
        }
        return $modelRepository;
    }

    public function toHtml()
    {
        $slider = $this->createSlider();
        return $slider->getStatus() ? parent::toHtml() : '';
    }
}
