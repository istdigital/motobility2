<?php

namespace MW\EasyFaq\Controller\Index;

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
        \MW\EasyFaq\Model\FaqCategoryFactory $categoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MW\EasyFaq\Model\FaqFactory $faqFactory,
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
        //       ->createBlock('MW\EasyFaq\Block\Faq\Index')
        //       ->toHtml();
        echo $this->jsonHelper->jsonEncode([
            'results' => $resultPage->getLayout()->createBlock('MW\EasyFaq\Block\Faq\Index')->toHtml(),
            'sidebar' => $resultPage->getLayout()->createBlock('MW\EasyFaq\Block\Category\Index')->setTemplate("MW_EasyFaq::category.phtml")->toHtml(),
        ]);
        exit();

        $searchKey = $this->getRequest()->getParam('key_search', '');
        $layoutConfig = $this->scopeConfig->getValue('easyfaq/general/layout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $faqJsonData = $this->getFaqSearchJsData($searchKey);
        echo $faqJsonData;
    }

    public function getFaqSearchJsData($searchKey)
    {
        $data = [];
        $data['name'] = __("Search Result");
        $data['category_id'] = 0;
        $faqs = $this->faqFactory->create()->getCollection()
            ->addFieldToFilter('status',1)
            ->getBySortOrder();
        if($searchKey){
            $faqs->addFieldToFilter(
                array(
                    'question',
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
        $data['faq_items'] = $faqs->getData();
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
            $data[$i]['faq_items'] = $this->faqFactory->create()->getFaqByCategoryId($val->getCategoryId())
                ->getBySortOrder()->getData();
            $i++;
        }
        return $this->jsonHelper->jsonEncode($data);
    }
}