<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\AjaxCompare\Plugin\Product\Compare;

use TemplateMonster\AjaxCompare\Helper\Product\Compare\AjaxResponse;

class Remove
{

    /**
     * @var
     */
    protected $_helper;

    /**
     * Remove constructor.
     * @param AjaxResponse $helper
     */
    public function __construct(AjaxResponse $helper)
    {
        $this->_helper = $helper;
    }

    /**
     * @param $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundExecute($subject, \Closure $proceed)
    {
        $request = $subject->getRequest();

        $returnValue = $proceed();
        if ($request->isXmlHttpRequest()) {
            return $this->_helper->getResult($subject, array('success'=>'1'));
        }
        return $returnValue;
    }
}
