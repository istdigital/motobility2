<?php

namespace TemplateMonster\Parallax\Block\Widget;

use TemplateMonster\Parallax\Helper\Data as ParallaxHelper;
use Magento\Customer\Model\Url;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Element\Template;

/**
 * Parallax Configure block.
 *
 * @package TemplateMonster\Parallax\Block
 */
class Configure extends Template
{
    /**
     * @var ParallaxHelper
     */
    protected $_parallaxHelper;

    /**
     * @var JsonHelper
     */
    protected $_jsonHelper;

    /**
     * @var string
     */
    protected $_template = 'configure.phtml';

    /**
     * Configure constructor.
     *
     * @param ParallaxHelper     $parallaxHelper
     * @param JsonHelper         $jsonHelper
     * @param Template\Context   $context
     * @param array              $data
     */
    public function __construct(
        ParallaxHelper $parallaxHelper,
        JsonHelper $jsonHelper,
        Template\Context $context,
        array $data = []
    )
    {
        $this->_parallaxHelper = $parallaxHelper;
        $this->_jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }
}