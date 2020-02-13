<?php
namespace TemplateMonster\AjaxCatalog\Block;

use Magento\Framework\View\Element\Template;
use TemplateMonster\AjaxCatalog\Model\PriceSlider as PriceSliderModel;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Json\EncoderInterface;

class PriceSlider extends Template
{
    protected $_template = 'TemplateMonster_AjaxCatalog::price_range.phtml';

    protected $_options = [];

    protected $_jsonEncoder;

    protected $_request;

    protected $_urlBuilder;

    public $filterItems;

    public function __construct(
        Context $context,
        PriceSliderModel $priceSliderModel,
        EncoderInterface $jsonEncoder,
        array $data = []
    ){
        $this->_priceSliderModel = $priceSliderModel;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_request = $context->getRequest();
        $this->_urlBuilder = $context->getUrlBuilder();

        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function setFilterItems($filterItems)
    {
        $this->filterItems = $filterItems;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFilterItems()
    {
        return $this->filterItems;
    }


    /**
     * @return mixed
     */
    public function getMaxPrice()
    {
        return $this->_priceSliderModel->getMaxPrice();
    }

    /**
     * @return mixed
     */
    public function getMinPrice()
    {
        return $this->_priceSliderModel->getMinPrice();
    }

    /**
     * Get options in json
     */
    public function getOptionsJson()
    {
        $this->_options = [
            'minPrice' => $this->getMinPrice(),
            'maxPrice' => $this->getMaxPrice(),
            'range' => array($this->getMinPrice(), $this->getMaxPrice()),
            'url' => $this->getCurrentUri(),
            'defaultValue' => $this->getMinPrice() . '-' . $this->getMaxPrice()
        ];

        return $this->_jsonEncoder->encode($this->_options);
    }

    /**
     * @return string
     */
    public function getCurrentUri()
    {
        $result = $this->buildUrl('price', $this->getMinPrice() . '-' . $this->getMaxPrice());

        return $result;
    }

    /**
     * Build URL method from swatchers module block
     *
     * @param string $attributeCode
     * @param int $optionId
     * @return string
     */
    public function buildUrl($attributeCode, $optionId)
    {
        $query = [$attributeCode => $optionId];
        return $this->_urlBuilder->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }


}