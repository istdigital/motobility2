<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Blocks;

use Magento\Framework\View\Element\Template;
use Manadev\LayeredNavigation\EngineFilter;
use Manadev\LayeredNavigation\UrlGenerator;

class AppliedItemRenderer extends Template
{
    /**
     * @var UrlGenerator
     */
    protected $urlGenerator;

    public function __construct(Template\Context $context, UrlGenerator $urlGenerator, array $data = []) {
        parent::__construct($context, $data);
        $this->urlGenerator = $urlGenerator;
    }

    public function render(EngineFilter $engineFilter, $item)
    {
        $this->setTemplate($engineFilter->getAppliedItemTemplateFilename());

        $this->assign('item', $item);
        $this->assign('filter', $engineFilter->getFilter());
        $this->assign('engineFilter', $engineFilter);

        $html = $this->_toHtml();

        $this->assign('item', null);
        $this->assign('filter', null);
        $this->assign('engineFilter', null);

        return $html;
    }

    public function getRemoveUrl(EngineFilter $engineFilter, $item) {
        return $this->escapeUrl($this->urlGenerator->getRemoveItemUrl($engineFilter, $item));
    }

    /**
     * @param EngineFilter $engineFilter
     * @param $item
     * @return string
     */
    public function getRemoveLinkAttributes($engineFilter, $item) {
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
}