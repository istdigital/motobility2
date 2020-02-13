<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Zemez\Repayment\Block\Adminhtml;

use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Index action.
 */
class View extends \Magento\Backend\Block\Template
{
    protected $_coreRegistry = null;

    protected $_backendUrl = null;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_backendUrl = $backendUrl;
        parent::__construct($context, $data);
    }

    public function getEnquiry(){
        return $this->_coreRegistry->registry('repayment_enquiry');
    }

    public function getCustomerUrl($id){
        return $this->_backendUrl->getUrl('customer/index/edit',['id' => $id]);
    }
    public function getProductUrl($id){
        return $this->_backendUrl->getUrl('catalog/product/edit',['id' => $id]);
    }
    
}
