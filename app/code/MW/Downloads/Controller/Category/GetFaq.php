<?php

namespace MW\Downloads\Controller\Category;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class GetDownloads extends Action
{
    protected $faqFactory;
    protected $jsonHelper;

    public function __construct
    (
        Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \MW\Downloads\Model\DownloadsFactory $faqFactory
    )
    {
        $this->jsonHelper = $jsonHelper;
        $this->faqFactory = $faqFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $categoryId = $this->getRequest()->getParam('category_id', 0);
        $faqJsonData = $this->getDownloadsByCategoryJsData($categoryId);
        echo $faqJsonData;
    }

    public function getDownloadsByCategoryJsData($categoryId)
    {
        $data = [];
        $dataJs = [];
        if($categoryId){
            $downloads = $this->faqFactory->create()->getDownloadsByCategoryId($categoryId)
                ->addFieldToFilter('status',1)
                ->getBySortOrder();
            $data['category_id'] = $categoryId;
            $data['faq_items'] = $downloads->getData();
            $dataJs[] = $data;
            return $this->jsonHelper->jsonEncode($dataJs);
        }
    }
}