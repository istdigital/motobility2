<?php

namespace MW\Downloads\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Search extends Action
{
    protected $faqFactory;
    protected $jsonHelper;
    protected $scopeConfig;
    protected $storeManager;
    protected $categoryFactory;
    protected $_resultPageFactory;

    public function __construct
    (
        Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \MW\Downloads\Model\DownloadsCategoryFactory $categoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MW\Downloads\Model\DownloadsFactory $faqFactory,
        PageFactory $resultPageFactory
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->faqFactory = $faqFactory;
        $this->scopeConfig = $scopeConfig;
        $this->categoryFactory = $categoryFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        // echo $resultPage->getLayout()
        //       ->createBlock('MW\Downloads\Block\Downloads\Index')
        //       ->toHtml();
        echo $this->jsonHelper->jsonEncode([
            'results' => $resultPage->getLayout()->createBlock('MW\Downloads\Block\Downloads\Index')->toHtml(),
            'sidebar' => $resultPage->getLayout()->createBlock('MW\Downloads\Block\Category\Index')->setTemplate("MW_Downloads::category.phtml")->toHtml(),
        ]);
        exit();

        $searchKey = $this->getRequest()->getParam('key_search', '');
        $layoutConfig = $this->scopeConfig->getValue('downloads/general/layout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $faqJsonData = $this->getDownloadsSearchJsData($searchKey);
        echo $faqJsonData;
    }

    public function getDownloadsSearchJsData($searchKey)
    {
        $data = [];
        $data['name'] = __("Search Result");
        $data['category_id'] = 0;
        $downloads = $this->faqFactory->create()->getCollection()
            ->addFieldToFilter('status',1)
            ->getBySortOrder();
        if($searchKey){
            $downloads->addFieldToFilter(
                array(
                    'title',
                    'answer'
                ),
                array(
                    array('like' => "%".$searchKey."%"),
                    array('like' => "%".$searchKey."%")
                )
            );
        }
        else{
            return $this->getDefaultDataJson();
        }
        $data['faq_items'] = $downloads->getData();
        $dataJS = [];
        $dataJS[] = $data;
        return $this->jsonHelper->jsonEncode($dataJS);
    }

    public function getDefaultDataJson()
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
}