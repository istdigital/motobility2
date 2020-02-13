<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Block\Adminhtml\SliderItem\Widget\Image\Canvas;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Cms\Helper\Wysiwyg\Images;

class Input extends Template
{

    protected $_image;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param array $data
     */
    public function __construct(Context $context, Images $image, array $data = [])
    {
        $this->_image = $image;

        $this->jsLayout = $this->getImageBasePath();
        parent::__construct($context, $data);
    }

    protected function getImageBasePath()
    {
        return ['basePathWysiwygImage'=>$this->_image->getCurrentUrl()];
    }
}
