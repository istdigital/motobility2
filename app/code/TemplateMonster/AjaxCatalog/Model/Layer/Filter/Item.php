<?php

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\AjaxCatalog\Model\Layer\Filter;

class Item extends \Magento\Catalog\Model\Layer\Filter\Item
{

    private $request;

    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Theme\Block\Html\Pager $htmlPagerBlock,
        \Magento\Framework\App\RequestInterface $request,
        array $data = []
    ) {
        $this->request = $request;
        parent::__construct($url,$htmlPagerBlock,$data);
    }

    /**
     *
     * Overwrite default action.
     * Check if current filter already has been applied
     * Create remove url from array
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRemoveUrl()
    {

        $request = $this->request;
        $currentValue = $this->getValue();
        $requestValue = $this->getFilter()->getRequestVar();

        if($request->getParam($requestValue)
            && is_array($request->getParam($requestValue))
        ) {

            $removeValue = array_diff($request->getParam($requestValue),[$currentValue]);

            if(count($removeValue) == 1) {
                $removeValue = current($removeValue);
            }

            $query = [$this->getFilter()->getRequestVar() => $removeValue];

            $params['_current'] = true;
            $params['_use_rewrite'] = true;
            $params['_query'] = $query;
            $params['_escape'] = true;
            return $this->_url->getUrl('*/*/*', $params);
        }
        return parent::getRemoveUrl();
    }

}