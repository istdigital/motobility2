<?php

namespace MW\EasyFaq\Controller\Category;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class GetFaq extends Action
{
    protected $faqFactory;
    protected $jsonHelper;

    public function __construct
    (
        Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \MW\EasyFaq\Model\FaqFactory $faqFactory
    )
    {
        $this->jsonHelper = $jsonHelper;
        $this->faqFactory = $faqFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $categoryId = $this->getRequest()->getParam('category_id', 0);
        $faqJsonData = $this->getFaqByCategoryJsData($categoryId);
        echo $faqJsonData;
    }

    public function getFaqByCategoryJsData($categoryId)
    {
        $data = [];
        $dataJs = [];
        if($categoryId){
            $faqs = $this->faqFactory->create()->getFaqByCategoryId($categoryId)
                ->addFieldToFilter('status',1)
                ->getBySortOrder();
            $data['category_id'] = $categoryId;
            $data['faq_items'] = $faqs->getData();
            $dataJs[] = $data;
            return $this->jsonHelper->jsonEncode($dataJs);
        }
    }
}