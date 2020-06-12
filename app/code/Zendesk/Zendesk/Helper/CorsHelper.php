<?php

namespace Zendesk\Zendesk\Helper;

class CorsHelper
{
    const ZENDESK_API_PATH_PATTERN = '#.*/V1/zendesk/.+#';

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * CorsHelper constructor.
     * @param Config $configHelper
     */
    public function __construct(
        \Zendesk\Zendesk\Helper\Config $configHelper
    ) {
        $this->configHelper = $configHelper;
    }

    /**
     * Get valid Zendesk API CORS request methods
     *
     * @return array
     */
    public function getZendeskApiCorsMethods()
    {
        return [
            \Zend\Http\Request::METHOD_OPTIONS,
            \Zend\Http\Request::METHOD_GET
        ];
    }

    /**
     * Determine if request is consistent with Zendesk API
     * CORS request.
     *
     * @param \Zend\Http\Request $request
     * @return bool
     */
    public function isZendeskCorsRequest(\Zend\Http\Request $request)
    {
        return in_array($request->getMethod(), $this->getZendeskApiCorsMethods())
            && preg_match(self::ZENDESK_API_PATH_PATTERN, $request->getPathInfo());
    }

    /**
     * Add the following CORS headers to request.
     * - Access-Control-Allow-Origin
     * - Access-Control-Allow-Methods
     * - Access-Control-Allow-Headers
     *
     * @param \Zend\Http\Request $request
     * @param \Magento\Framework\Webapi\Rest\Response $response
     */
    public function addCorsHeaders(
        \Zend\Http\Request $request,
        \Magento\Framework\Webapi\Rest\Response $response
    ) {
        $origin = $request->getHeader('Origin');

        if (!preg_match($this->configHelper->getZendeskAppCorsOrigin(), $origin)) {
            // CORS origin is not a valid Zendesk app request -- bail without adding any access control headers.

            return;
        }

        $response->setHeader('Access-Control-Allow-Origin', $origin);
        $response->setHeader('Access-Control-Allow-Methods', $request->getHeader('Access-Control-Request-Method'));
        $response->setHeader('Access-Control-Allow-Headers', $request->getHeader('Access-Control-Request-Headers'));
    }
}
