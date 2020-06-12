<?php

namespace Zendesk\Zendesk\Plugin\Webapi\Controller\Rest;

use Magento\Webapi\Controller\Rest\Router as OriginalRouter;

class Router
{
    const ROUTE_PATH = '/V1/zendesk/cors';

    /**
     * @var \Magento\Framework\Controller\Router\Route\Factory
     */
    protected $routeFactory;
    /**
     * @var \Zendesk\Zendesk\Helper\CorsHelper
     */
    protected $corsHelper;

    /**
     * Router constructor.
     * @param \Magento\Framework\Controller\Router\Route\Factory $routeFactory
     * @param \Zendesk\Zendesk\Helper\CorsHelper $corsHelper
     */
    public function __construct(
        \Magento\Framework\Controller\Router\Route\Factory $routeFactory,
        \Zendesk\Zendesk\Helper\CorsHelper $corsHelper
    ) {
        $this->routeFactory = $routeFactory;
        $this->corsHelper = $corsHelper;
    }

    /**
     * If webapi request is OPTIONS, manually match to Zendesk CORS API route
     *
     * @param OriginalRouter $subject
     * @param callable $proceed
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @return OriginalRouter\Route
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function aroundMatch(
        OriginalRouter $subject,
        callable $proceed,
        \Magento\Framework\Webapi\Rest\Request $request
    ) {
        try {
            // Optimistically try to simply return original method's return value.
            return $proceed($request);
        } catch (\Magento\Framework\Webapi\Exception $ex) {
            // This exception indicates the request is not routable

            if ($this->corsHelper->isZendeskCorsRequest($request)) {
                // If request is actually Zendesk CORS request, manually route.
                return $this->getCorsRoute();
            } else {
                // Otherwise, allow exception
                throw $ex;
            }
        }
    }

    /**
     * Get CORS API route
     *
     * @return OriginalRouter\Route
     */
    protected function getCorsRoute()
    {
        /** @var $route \Magento\Webapi\Controller\Rest\Router\Route */
        $route = $this->routeFactory->createRoute(
            \Magento\Webapi\Controller\Rest\Router\Route::class,
            self::ROUTE_PATH
        );

        $route->setServiceClass(\Zendesk\Zendesk\Api\CorsRepositoryInterface::class)
            ->setServiceMethod('accessControlAllowOrigin')
            ->setAclResources([\Magento\Integration\Api\AuthorizationServiceInterface::PERMISSION_ANONYMOUS]);

        return $route;
    }
}
