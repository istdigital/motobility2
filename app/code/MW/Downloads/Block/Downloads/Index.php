<?php

namespace MW\Downloads\Block\Downloads;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

class Index extends Template
{
    protected $jsonHelper;
    protected $categoryFactory;
    protected $faqFactory;
    protected $storeManager;
    protected $scopeConfig;
    protected $_urlBuilder;

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
        $this->_urlBuilder = $context->getUrlBuilder();;
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
        $page_title = $this->scopeConfig->getValue('downloads/general/page_title', ScopeInterface::SCOPE_STORE);
        $meta_title = $this->scopeConfig->getValue('downloads/general/meta_title', ScopeInterface::SCOPE_STORE);
        $meta_keywords = $this->scopeConfig->getValue('downloads/general/meta_keywords', ScopeInterface::SCOPE_STORE);
        $meta_description = $this->scopeConfig->getValue('downloads/general/meta_description', ScopeInterface::SCOPE_STORE);

        $page = $this->getPage();
        $this->pageConfig->addBodyClass('downloads');

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
                    'downloads',
                    [
                        'label' => __('Downloads'),
                        'title' => __('Downloads'),
                        'link' => $this->getUrl('downloads')
                    ]
                );

                $category = $this->categoryFactory->create()->load($catid);
                $breadcrumbsBlock->addCrumb(
                    'downloads_category',
                    [
                        'label' => $category->getName(),
                        'title' => $category->getName(),
                    ]
                );
            }else{
                $breadcrumbsBlock->addCrumb('downloads', ['label' => __("Downloads"), 'title' => __("Downloads")]);
            }

            
        }

        

        return parent::_prepareLayout();
    }



    public function getDownloadUrl($f)
    {
        return $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $f;
    }


    public function getDownloadsCollection()
    {
        $collection = $this->getCollection();

        if ($collection && $collection->getSize()) {
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'downloads.pager');

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
                    'title',
                    'tags'
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