<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Block\Adminhtml\Slider\Edit\Tab\Slides\Column;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

class Image extends AbstractRenderer
{

    protected $_storeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\Backend\Block\Context $context, array $data = [])
    {
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }


    public function render(DataObject $row)
    {
        $image = $row->getImage();
        $wysiwygPath = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $fullImgPath = $wysiwygPath.$image;
        return '<img width="400px" src="'.$fullImgPath.'"/>';
    }
}
