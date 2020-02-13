<?php

/**
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace TemplateMonster\AjaxCatalog\Plugin\Brand;

use TemplateMonster\AjaxCatalog\Helper\Catalog\View\ContentAjaxResponse;

class View
{
    /**
     * @var ContentAjaxResponse
     */
    protected $_helper;

    public function __construct(ContentAjaxResponse $helper)
    {
        $this->_helper = $helper;
    }

    public function aroundExecute($subject, \Closure $proceed)
    {
        $request = $subject->getRequest();
        if ($request->isXmlHttpRequest()) {

            return $this->_helper->getAjaxContent($subject, $proceed);
        } else {
            $returnValue = $proceed();

            return $returnValue;
        }
    }
}
