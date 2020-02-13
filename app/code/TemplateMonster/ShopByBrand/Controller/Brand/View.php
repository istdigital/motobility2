<?php

namespace TemplateMonster\ShopByBrand\Controller\Brand;

use TemplateMonster\ShopByBrand\Api\BrandRepositoryInterface;
use TemplateMonster\ShopByBrand\Api\Data\BrandInterface;
use TemplateMonster\ShopByBrand\Helper\Data as ShopByBrandHelper;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry as CoreRegistry;

/**
 * Brand view page.
 *
 * @package TemplateMonster\ShopByBrand\Controller\Brand
 */
class View extends Action
{
    /**
     * @var ShopByBrandHelper
     */
    protected $_helper;

    /***
     * @var CoreRegistry
     */
    protected $_coreRegistry;

    /**
     * @var BrandRepositoryInterface
     */
    protected $_brandRepository;

    /**
     * @var BrandInterface|null
     */
    private $_brand = null;

    /**
     * View constructor.
     *
     * @param ShopByBrandHelper        $helper
     * @param CoreRegistry             $coreRegistry
     * @param BrandRepositoryInterface $brandRepository
     * @param Context                  $context
     */
    public function __construct(
        ShopByBrandHelper $helper,
        CoreRegistry $coreRegistry,
        BrandRepositoryInterface $brandRepository,
        Context $context
    ) {
        $this->_helper = $helper;
        $this->_coreRegistry = $coreRegistry;
        $this->_brandRepository = $brandRepository;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $id = (int) $this->getRequest()->getParam('brand_id');

        return $this->_initPage($this->_getBrand($id));
    }

    /**
     * @param int $id
     *
     * @return BrandInterface
     */
    protected function _getBrand($id)
    {
        if (null === $this->_brand) {
            $brand = $this->_brandRepository->getById($id);
            $this->_coreRegistry->register('current_brand', $brand);
            $this->_brand = $brand;
        }

        return $this->_brand;
    }

    /**
     * Init page.
     *
     * @param BrandInterface $brand
     *
     * @return \Magento\Framework\View\Result\Page
     */
    protected function _initPage(BrandInterface $brand)
    {
        /** @var \Magento\Framework\View\Result\Page $page */
        $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        //$page->addHandle('catalog_category_view_type_layered');

        $page->getConfig()->setPageLayout($this->_helper->getBrandLayout());
        $page->getConfig()->getTitle()->set(__($brand->getPageTitle()));

        if ($breadcrumbs = $page->getLayout()->getBlock('breadcrumbs')) {
            /** @var \Magento\Theme\Block\Html\Breadcrumbs $breadcrumbs */
            $breadcrumbs->addCrumb('brands', [
                'label' => __('Brand'),
                'title' => __('Go to Brand Page'),
                'link' => $this->_url->getUrl('brand')
            ]);
            $breadcrumbs->addCrumb('brands', [
                'label' => __('Brands'),
                'title' => __('Go to Brand Page'),
                'link' => $this->_url->getUrl('brand')
            ]);
            $breadcrumbs->addCrumb('brand_view', [
                'label' => $brand->getName(),
                'title' => $brand->getName(),
            ]);
        }

        if ($description = $brand->getMetaDescription()) {
            $page->getConfig()->setDescription(__($description));
        }

        if ($keywords = $brand->getMetaKeywords()) {
            $page->getConfig()->setKeywords(__($keywords));
        }

        return $page;
    }
}