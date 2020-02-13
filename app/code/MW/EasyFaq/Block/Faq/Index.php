<?php

namespace MW\EasyFaq\Block\Faq;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

class Index extends Template
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
        \MW\EasyFaq\Model\FaqCategoryFactory $categoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MW\EasyFaq\Model\FaqFactory $faqFactory,
        array $data = []
    )
    {
        if($scopeConfig->getValue('easyfaq/general/layout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == \MW\EasyFaq\Model\Source\Config\Layout::ONE_COLUMN){
            $this->setTemplate('MW_EasyFaq::1column/faq.phtml');
        }
        else{
            $this->setTemplate("MW_EasyFaq::faq.phtml");
        }
        $this->jsonHelper = $jsonHelper;
        $this->categoryFactory = $categoryFactory;
        $this->faqFactory = $faqFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $page_title = $this->scopeConfig->getValue('easyfaq/general/page_title', ScopeInterface::SCOPE_STORE);
        $meta_title = $this->scopeConfig->getValue('easyfaq/general/meta_title', ScopeInterface::SCOPE_STORE);
        $meta_keywords = $this->scopeConfig->getValue('easyfaq/general/meta_keywords', ScopeInterface::SCOPE_STORE);
        $meta_description = $this->scopeConfig->getValue('easyfaq/general/meta_description', ScopeInterface::SCOPE_STORE);

        $page = $this->getPage();
        $this->pageConfig->addBodyClass('easyfaq');

        $this->pageConfig->getTitle()->set($page_title);
        $this->pageConfig->setMetaTitle($meta_title);
        $this->pageConfig->setKeywords($meta_keywords);
        $this->pageConfig->setDescription($meta_description);


        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
        if($breadcrumbsBlock){

            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->storeManager->getStore()->getBaseUrl()
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'resources',
                [
                    'label' => __('Resources'),
                    'title' => __('Go to Resources'),
                    'link' => $this->getUrl('resources')
                ]
            );
            
            if(!empty($catid = $this->getRequest()->getParam('cat'))){
                $breadcrumbsBlock->addCrumb(
                    'faq',
                    [
                        'label' => __('Frequently Asked Questions'),
                        'title' => __('Frequently Asked Questions'),
                        'link' => $this->getUrl('faqs')
                    ]
                );

                $category = $this->categoryFactory->create()->load($catid);
                $breadcrumbsBlock->addCrumb(
                    'faq_category',
                    [
                        'label' => $category->getName(),
                        'title' => $category->getName(),
                    ]
                );
            }else{
                $breadcrumbsBlock->addCrumb('faq', ['label' => __("Frequently Asked Questions"), 'title' => __("Frequently Asked Questions")]);
            }

            
        }

        

        return parent::_prepareLayout();
    }

    public function getFaqCollection()
    {
        $collection = $this->getCollection();

        if ($collection && $collection->getSize()) {
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'faqs.pager');

            $pager->setCollection($collection);

            $this->setChild('pager', $pager);
        }

        return $collection;
    }

    public function getCollection(){


        $collection = $this->faqFactory->create()
            ->getCollection()
            ->addFieldToFilter('status',1);

        
        if(!empty($query = $this->getRequest()->getParam('query'))){
            $collection->addFieldToFilter(
                array(
                    'question',
                    'answer'
                ),
                array(
                    array('like' => "%".$query."%"),
                    array('like' => "%".$query."%")
                )
            );
        }
        if(isset($_GET['cat']) && !empty($_GET['cat'])){
            $collection->addFieldToFilter('category_id',$_GET['cat']);
        }


        return $collection;
    }


    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
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
            $data[$i]['faq_items'] = $this->faqFactory->create()->getFaqByCategoryId($val->getCategoryId())
                ->getBySortOrder()->getData();
            $i++;
        }
        return $this->jsonHelper->jsonEncode($data);
    }

    public function isAjaxPageType()
    {
        $pageStyleConfig = $this->scopeConfig->getValue('easyfaq/general/page_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($pageStyleConfig == \MW\EasyFaq\Model\Source\Config\PageType::AJAX){
            return true;
        }
        return false;
    }
}