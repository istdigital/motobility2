<?php
/**
 * Mage-World
 *
 *  @category    Mage-World
 *  @package     MW
 *  @author      Mage-world Developer
 *
 *  @copyright   Copyright (c) 2018 Mage-World (https://www.mage-world.com/)
 */

namespace MW\Downloads\Block\Category;

use Magento\Framework\View\Element\Template;

class Index extends Template
{
    protected $categoryFactory;
    protected $jsonHelper;
    protected $storeManager;
    protected $scopeConfig;

    public function __construct
    (
        Template\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \MW\Downloads\Model\DownloadsCategoryFactory $categoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    )
    {
        $this->jsonHelper = $jsonHelper;
        $this->categoryFactory = $categoryFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    public function getCategories()
    {
        $storeIds = [0];
        array_push($storeIds, $this->storeManager->getStore()->getStoreId());
        return $this->categoryFactory->create()
            ->getCollection()
            ->addFieldToFilter('status',1)
            ->addFieldToFilter('store_id',array('in'=>$storeIds))
            ->getBySortOrder();
    }

    public function getCategoriesJsData()
    {
        return $this->jsonHelper->jsonEncode($this->getCategories()->getData());
    }

    public function getLoadingPath()
    {
        return $this ->getViewFileUrl('images/loader-2.gif');
    }

    public function isAjaxPageType()
    {
        $pageStyleConfig = $this->scopeConfig->getValue('downloads/general/page_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($pageStyleConfig == \MW\Downloads\Model\Source\Config\PageType::AJAX){
            return true;
        }
        return false;
    }
}