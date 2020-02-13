<?php

namespace TemplateMonster\AjaxCatalog\Plugin\LayeredNavigation\Block\Navigation;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\View\Element\Template\Context;
use TemplateMonster\AjaxCatalog\Block\PriceSlider;
use Magento\Store\Model\ScopeInterface as Scope;


class FilterRenderer
{
    const PRICE_FILTER_CLASS_NAME = 'Magento\CatalogSearch\Model\Layer\Filter\Price';
    const FILTER_TEMPLATE = 'Magento_LayeredNavigation::layer/filter.phtml';
    const PRICE_CONFIG = 'ajaxcatalog/general/ajaxcatalog_show_price_slider';

    public function __construct(
        Context $context,
        PriceSlider $priceSlider,
        array $data = [])
    {
        $this->_priceSlider = $priceSlider;
        $this->_scopeConfig = $context->getScopeConfig();
    }

    /**
     * @param \Magento\LayeredNavigation\Block\Navigation\FilterRenderer $subject
     * @param callable $proceed
     * @param FilterInterface $filter
     * @return string
     */
    public function aroundRender(
        \Magento\LayeredNavigation\Block\Navigation\FilterRenderer $subject,
        callable $proceed,
        FilterInterface $filter
    )
    {
        if(!$this->isEnabled()){
            return $proceed($filter);
        }

        $filters = $filter->getItems();

        if(!is_a($filter, self::PRICE_FILTER_CLASS_NAME)){
            $subject->assign('filterItems', $filters);
            $subject->setTemplate(self::FILTER_TEMPLATE);
            $html = $subject->toHtml();
            $subject->assign('filterItems', []);
        } else {

            $html = $this->_priceSlider->setFilterItems($filters)->toHtml();
        }

        return $html;
    }

    /**
     * @return bool
     */
    protected function isEnabled()
    {
        return $this->_scopeConfig->isSetFlag(self::PRICE_CONFIG, Scope::SCOPE_STORE);
    }
}