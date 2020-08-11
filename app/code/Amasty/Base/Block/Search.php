<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


namespace Amasty\Base\Block;

use Amasty\Base\Model\ModuleInfoProvider;
use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Search block
 */
class Search extends Template implements RendererInterface
{
    protected $_template = 'Amasty_Base::search.phtml';

    /**
     * @var ModuleInfoProvider
     */
    private $moduleInfoProvider;

    public function __construct(
        Template\Context $context,
        ModuleInfoProvider $moduleInfoProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleInfoProvider = $moduleInfoProvider;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setData('cache_lifetime', 86400);
    }

    /**
     * Render Search html
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        return $this->toHtml();
    }

    /**
     * @return string
     */
    public function getSearchBaseUrl()
    {
        $baseUrl = 'https://amasty.com/catalogsearch/result/?q=';

        if ($this->moduleInfoProvider->isOriginMarketplace()) {
            $baseUrl = 'https://marketplace.magento.com/catalogsearch/result/?q=Amasty%20';
        }

        return $baseUrl;
    }

    /**
     * @return string
     */
    public function getSearchUrlParams()
    {
        $params = '&utm_source=extension&utm_medium=extnotif&utm_campaign=searchbar';

        if ($this->moduleInfoProvider->isOriginMarketplace()) {
            $params = '&categories=Extensions&ext_platform=Magento%202';
        }

        return $params;
    }
}
