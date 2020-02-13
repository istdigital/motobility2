<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace TemplateMonster\ShopByBrand\Block\Adminhtml\Page\Edit;

use Magento\Backend\Block\Widget\Context;
use TemplateMonster\ShopByBrand\Api\BrandRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var BrandRepositoryInterface
     */
    protected $brandRepository;

    /**
     * GenericButton constructor.
     * @param Context $context
     * @param BrandRepositoryInterface $brandRepository
     */
    public function __construct(
        Context $context,
        BrandRepositoryInterface $brandRepository
    ) {
        $this->context = $context;
        $this->brandRepository = $brandRepository;
    }

    /**
     * @return null
     */
    public function getPageId()
    {
        try {
            return $this->brandRepository->getById(
                $this->context->getRequest()->getParam('brand_id')
            )->getId();
        } catch (NoSuchEntityException $e) {
        }
        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
