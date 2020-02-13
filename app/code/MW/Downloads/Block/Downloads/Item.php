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

namespace MW\Downloads\Block\Downloads;

use Magento\Framework\View\Element\Template;

class Item extends Template
{
    protected $jsonHelper;
    protected $categoryFactory;
    protected $faqFactory;
    protected $storeManager;
    protected $scopeConfig;

    public function __construct
    (
        Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \MW\Downloads\Model\DownloadsCategoryFactory $categoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MW\Downloads\Model\DownloadsFactory $faqFactory,
        array $data = []
    )
    {
        if($scopeConfig->getValue('downloads/general/layout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == \MW\Downloads\Model\Source\Config\Layout::ONE_COLUMN){
            $this->setTemplate('MW_Downloads::1column/downloads.phtml');
        }
        else{
            $this->setTemplate("MW_Downloads::downloads.phtml");
        }
        $this->jsonHelper = $jsonHelper;
        $this->categoryFactory = $categoryFactory;
        $this->faqFactory = $faqFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    public function getDataOneColumnJson()
    {
        $storeIds = [0];
        array_push($storeIds, $this->storeManager->getStore()->getStoreId());
        $data = [];
        $i = 0;
        $categories = $this->categoryFactory->create()
            ->getCollection()
            ->addFieldToFilter('status',1)
            ->addFieldToFilter('store_id',array('in'=>$storeIds))
            ->getBySortOrder();
        foreach($categories as $val){
            $data[$i] = $val->getData();
            $data[$i]['faq_items'] = $this->faqFactory->create()->getDownloadsByCategoryId($val->getCategoryId())
                ->getBySortOrder()->getData();
            $i++;
        }
        return $this->jsonHelper->jsonEncode($data);
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