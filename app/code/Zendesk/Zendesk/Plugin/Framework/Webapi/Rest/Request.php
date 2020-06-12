<?php

namespace Zendesk\Zendesk\Plugin\Framework\Webapi\Rest;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Webapi\Rest\Request as OriginalRequest;

class Request
{
    /**
     * @var \Zendesk\Zendesk\Helper\CorsHelper
     */
    protected $corsHelper;

    /**
     * Request constructor.
     * @param \Zendesk\Zendesk\Helper\CorsHelper $corsHelper
     */
    public function __construct(
        \Zendesk\Zendesk\Helper\CorsHelper $corsHelper
    ) {
        $this->corsHelper = $corsHelper;
    }

    /**
     * Permit OPTIONS method on REST requests to allow CORS check
     *
     * @param OriginalRequest $subject
     * @param callable $proceed
     * @return string
     * @throws InputException
     */
    public function aroundGetHttpMethod(OriginalRequest $subject, callable $proceed)
    {
        try {
            // Optimistically try to simply return original method's return value.
            return $proceed();
        } catch (InputException $inputException) {
            // If we get an input exception it must be due to invalid method.

            if ($this->corsHelper->isZendeskCorsRequest($subject)) {
                // However, we're going to permit Zendesk API CORS requests.
                return $subject->getMethod();
            } else {
                // Anything else is still a problem, so pass on exception.
                throw $inputException;
            }
        }
    }
}
