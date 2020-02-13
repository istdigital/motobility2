<?php

namespace TemplateMonster\ShopByBrand\Block\Widget;

use Magento\Framework\View\Element\Template;
use TemplateMonster\ShopByBrand\Api\Data\BrandInterfaceFactory;

class Brand extends Template implements \Magento\Widget\Block\BlockInterface
{

    /**
     * @var BrandInterfaceFactory
     */
    protected $brand;

    /**
     * @var string
     */
    protected $_template = 'TemplateMonster_ShopByBrand::widget/brand/default.phtml';

    /**
     * Brand constructor.
     * @param Template\Context $context
     * @param BrandInterfaceFactory $brand
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        BrandInterfaceFactory $brand,
        array $data)
    {
        $this->brand = $brand;
        parent::__construct($context, $data);
    }

    public function getBrandAmount()
    {
        return $this->getData('brands_amount');
    }

    public function getBrandPerView()
    {
        return $this->getData('brand_per_view');
    }

    public function getBrandsLogoWidth()
    {
        return $this->getData('brands_logo_width');
    }

    public function getShowBrandTitle()
    {
        return (bool)$this->getData('show_brand_title');
    }

    public function getBrands()
    {
        return $this->getData('brands');
    }

    /**
     * @return array
     */
    public function getBrandCollection()
    {
        $brandIds = $brandIds = explode(',',$this->getBrands());
        if (!$brandIds) {
           return [];
        }
        $brandCollection = $this->brand->create()->getResourceCollection();
        $brandCollection->addWebsiteFilter()->addFieldToFilter('status','1');
        $brandCollection->addFieldToFilter('brand_id',['in'=>$brandIds]);
        if ($this->getBrands()) {
            $brandCollection->getSelect()->limit($this->getBrandAmount());
        }
        return $brandCollection;
    }

    public function getItemWidth()
    {
        $productItemWidth = '';
        $productPerRow = $this->getBrandsPerRow();
        if ($productPerRow) {
            if (!$this->getShowCarousel()) {
                $productItemWidth = 'style="flex-basis: ' . 100 / $productPerRow . '%; width: ' . 100 / $productPerRow . '%;"';
            }
        }
        return $productItemWidth;
    }
}