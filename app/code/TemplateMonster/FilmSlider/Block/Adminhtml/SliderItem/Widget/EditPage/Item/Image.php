<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Block\Adminhtml\SliderItem\Widget\EditPage\Item;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Image extends Template
{

    protected $_storeManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data = [])
    {
        $this->_storeManager = $context->getStoreManager();
        $this->jsLayout = $this->getImageBasePath();
        parent::__construct($context, $data);
    }

    protected function getImageBasePath()
    {
        return [
            'basePathWysiwygImage'=>
                $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
        ];
    }
}
