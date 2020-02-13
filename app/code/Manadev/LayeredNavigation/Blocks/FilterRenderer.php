<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Blocks;

use Magento\Framework\View\Element\Template;
use Manadev\LayeredNavigation\EngineFilter;
use Manadev\LayeredNavigation\UrlGenerator;

class FilterRenderer extends Template
{
    /**
     * @var UrlGenerator
     */
    public $urlGenerator;
    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $catalogHelper;
    /**
     * @var \Magento\Swatches\Helper\Media
     */
    protected $mediaHelper;

    public function __construct(Template\Context $context,
        \Magento\Catalog\Helper\Data $catalogHelper, \Magento\Swatches\Helper\Media $mediaHelper,
        UrlGenerator $urlGenerator,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->urlGenerator = $urlGenerator;
        $this->catalogHelper = $catalogHelper;
        $this->mediaHelper = $mediaHelper;
    }

    public function render(EngineFilter $engineFilter)
    {
        $this->setTemplate($engineFilter->getTemplateFilename());

        $data = $engineFilter->getData();
        if (is_array($data)) {
            $data = array_filter($data, function($item) {
                return !is_array($item) || empty($item['hidden']);
            });
        }
        $this->assign('data', $data);
        $this->assign('filter', $engineFilter->getFilter());
        $this->assign('engineFilter', $engineFilter);

        $html = $this->_toHtml();

        $this->assign('data', null);
        $this->assign('filter', null);
        $this->assign('engineFilter', null);

        return $html;
    }

    public function shouldDisplayProductCountOnLayer() {
        return $this->catalogHelper->shouldDisplayProductCountOnLayer();
    }

    /**
     * @param EngineFilter $engineFilter
     * @param $item
     * @return string
     */
    public function getAddItemUrl(EngineFilter $engineFilter, $item) {
        return $this->escapeUrl($this->urlGenerator->getAddItemUrl($engineFilter, $item));
    }

    /**
     * @param EngineFilter $engineFilter
     * @param $item
     * @return string
     */
    public function getAddItemLinkAttributes($engineFilter, $item) {
        return $this->urlGenerator->renderLastAction();
    }

    public function getRemoveItemUrl(EngineFilter $engineFilter, $item = null) {
        return $this->escapeUrl($this->urlGenerator->getRemoveItemUrl($engineFilter, $item));
    }

    /**
     * @param EngineFilter $engineFilter
     * @param $item
     * @return string
     */
    public function getRemoveItemLinkAttributes($engineFilter, $item) {
        return $this->urlGenerator->renderLastAction();
    }

    public function getReplaceItemUrl($engineFilter, $item) {
        return $this->escapeUrl($this->urlGenerator->getReplaceItemUrl($engineFilter, $item));
    }

    /**
     * @param EngineFilter $engineFilter
     * @param $item
     * @return string
     */
    public function getReplaceItemLinkAttributes($engineFilter, $item) {
        return $this->urlGenerator->renderLastAction();
    }

    public function escapeItemLabel(EngineFilter $engineFilter, $label) {
        if ($engineFilter->isLabelHtmlEscaped()) {
            return $this->escapeHtml($label);
        }
        else {
            return $label;
        }
    }

    public function getRangeSliderApplyUrl(EngineFilter $engineFilter){
        return $this->urlGenerator->getMarkRangeUrl($engineFilter);
    }

    public function getMultiSelectSliderApplyUrl(EngineFilter $engineFilter) {
        return $this->urlGenerator->getMarkAddItemUrl($engineFilter);
    }

    public function getSwatchPath($type, $filename)
    {
        $imagePath = $this->mediaHelper->getSwatchAttributeImage($type, $filename);

        return $imagePath;
    }

    public function getFilterName($filter) {
        $navPosition = $this->getParentBlock()->getData('position');
        $attributeCode = $filter->getData('param_name');

        return "{$navPosition}-{$attributeCode}";
    }
}