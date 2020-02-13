<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Search term block
 */
namespace TemplateMonster\AjaxSearch\Block;

use Magento\Framework\UrlFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory;

class Term extends \Magento\Search\Block\Term
{

    /**
     * Url factory
     *
     * @var UrlFactory
     */
    protected $_urlFactory;

    /**
     * Query collection factory
     *
     * @var CollectionFactory
     */
    protected $_queryCollectionFactory;

    /**
     * @param Context $context
     * @param CollectionFactory $queryCollectionFactory
     * @param UrlFactory $urlFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $queryCollectionFactory,
        UrlFactory $urlFactory,
        array $data = []
    ) {
        $this->_queryCollectionFactory = $queryCollectionFactory;
        $this->_urlFactory = $urlFactory;
        parent::__construct($context, $queryCollectionFactory, $urlFactory, $data);
    }
}
