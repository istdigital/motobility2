<?php

namespace Yereone\Testimonials\Block;

use Magento\Framework\View\Element\Template;
use Yereone\Testimonials\Helper\Data;
use Magento\Store\Model\ScopeInterface;

class Index extends Template
{
    /**
     * @var \Yereone\Testimonials\Model\TestimonialFactory
     */
    protected $testimonialFactory;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;

    /**
     * @var \Yereone\Testimonials\Helper\Data
     */
    protected $helper;

    protected $scopeConfig;

    protected $storeManager;

    /**
     * @param \Yereone\Testimonials\Model\TestimonialFactory $testimonialFactory
     * @param Template\Context $context
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        \Yereone\Testimonials\Model\TestimonialFactory $testimonialFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Template\Context $context,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Yereone\Testimonials\Helper\Data $helper,
        array $data = []
    ) {
        $this->testimonialFactory = $testimonialFactory;
        $this->_imageFactory = $imageFactory;
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    public function getTestimonialsCollection()
    {
        $collection = $this->getCollection();

        if ($collection && $collection->getSize()) {
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'faqs.pager');

            $pager->setCollection($collection);

            $this->setChild('pager', $pager);
        }

        return $collection;
    }

    public function getCollection()
    {

        $collection = $this->testimonialFactory->create()
            ->getCollection()
            ->addFieldToFilter('status_id', 1);

        return $collection;
    }


    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $page_title = $this->scopeConfig->getValue('yereone_testimonials/general/page_title', ScopeInterface::SCOPE_STORE);
        $meta_title = $this->scopeConfig->getValue('yereone_testimonials/general/meta_title', ScopeInterface::SCOPE_STORE);
        $meta_keywords = $this->scopeConfig->getValue('yereone_testimonials/general/meta_keywords', ScopeInterface::SCOPE_STORE);
        $meta_description = $this->scopeConfig->getValue('yereone_testimonials/general/meta_description', ScopeInterface::SCOPE_STORE);

        $page = $this->getPage();
        $this->pageConfig->addBodyClass('testimonials');

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
            $breadcrumbsBlock->addCrumb('faq', ['label' => __("Testimonials"), 'title' => __("Testimonials")]);

            
        }

        

        return parent::_prepareLayout();
    }
}
