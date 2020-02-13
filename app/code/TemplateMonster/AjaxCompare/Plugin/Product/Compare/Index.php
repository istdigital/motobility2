<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\AjaxCompare\Plugin\Product\Compare;

use TemplateMonster\AjaxCompare\Helper\Product\Compare\AjaxResponse;
use Magento\Framework\View\Result\PageFactory;

class Index
{
    /**
     * @var AjaxResponse
     */
    protected $_helper;

    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * Index constructor.
     * @param AjaxResponse $helper
     * @param PageFactory $pageFactory
     */
    public function __construct(AjaxResponse $helper,
                                PageFactory $pageFactory)
    {
        $this->_helper = $helper;
        $this->_pageFactory = $pageFactory;
    }

    /**
     * @param $subject
     * @param \Closure $proceed
     * @return array
     */
    public function aroundExecute($subject, \Closure $proceed)
    {
        $request = $subject->getRequest();
        $result = $proceed();
        if ($request->isXmlHttpRequest()) {
            if (is_a($result, 'Magento\Framework\View\Result\Page')) {
                $page = $result;
            } else {
                $page = $this->_pageFactory->create();
            }

            $result = [];

            try {
                $result['content'] = $page->getLayout()->renderElement('content');
                $result['title'] = $page->getLayout()->renderElement('page.main.title');
                $result['success'] = true;
            } catch (\Exception $e) {
                $result['error'] = true;
                $result['message'] = 'Can not finished request';
            }

            return $this->_helper->getResult($subject, $result);
        }
        return $result;
    }
}
