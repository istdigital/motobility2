<?php

namespace Zendesk\Zendesk\Plugin\Webapi\Controller;

use \Magento\Webapi\Controller\Rest as OriginalRest;

class Rest
{
    /**
     * @var \Magento\Framework\Webapi\Rest\Response
     */
    protected $response;
    /**
     * @var \Zendesk\Zendesk\Helper\CorsHelper
     */
    protected $corsHelper;

    /**
     * Rest constructor.
     * @param \Magento\Framework\Webapi\Rest\Response $response
     * @param \Zendesk\Zendesk\Helper\CorsHelper $corsHelper
     */
    public function __construct(
        \Magento\Framework\Webapi\Rest\Response $response,
        \Zendesk\Zendesk\Helper\CorsHelper $corsHelper
    ) {
        $this->response = $response;
        $this->corsHelper = $corsHelper;
    }

    /**
     * Add CORS headers
     *
     * @param OriginalRest $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @return mixed
     */
    public function aroundDispatch(
        OriginalRest $subject,
        callable $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $return = $proceed($request);

        // Check interface implementation to ensure it has methods required by following method calls
        if ($request instanceof \Zend\Http\Request) {
            if ($this->corsHelper->isZendeskCorsRequest($request)) {
                $this->corsHelper->addCorsHeaders($request, $this->response);
            }
        }
        return $return;
    }
}
