<?php

namespace TemplateMonster\ShopByBrand\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultPageFactory;

    /**
     * @var
     */
    protected $brandRepository;

    /**
     * View constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \TemplateMonster\ShopByBrand\Api\BrandRepositoryInterface $brandRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \TemplateMonster\ShopByBrand\Api\BrandRepositoryInterface $brandRepository
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->brandRepository = $brandRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        $page = $this->resultPageFactory->create();
        $page->getConfig()->getTitle()->set(__('Brands'));
        $page->getConfig()->setDescription(__('Brands'));
        $page->getConfig()->setKeywords(__('Brands'));
        return $page;
    }
}