<?php

namespace Zendesk\Zendesk\ZendeskApi;

class HttpClient extends \Zendesk\API\HttpClient
{
    /**
     * @var \Zendesk\Zendesk\Helper\Config
     */
    protected $configHelper;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * HttpClient constructor.
     *
     * @param string $subdomain
     * @param \Zendesk\Zendesk\Helper\Config $configHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $username
     * @param string $scheme
     * @param string $hostname
     * @param int $port
     * @param \GuzzleHttp\Client|null $guzzle
     */
    public function __construct(
        $subdomain,
        // end parent required parameters
        \Zendesk\Zendesk\Helper\Config $configHelper,
        \Psr\Log\LoggerInterface $logger,
        // end custom required parameters
        $username = '',
        $scheme = "https",
        $hostname = "zendesk.com",
        $port = 443,
        \GuzzleHttp\Client $guzzle = null
    ) {
        parent::__construct($subdomain, $username, $scheme, $hostname, $port, $guzzle);
        $this->configHelper = $configHelper;
        $this->logger = $logger;
    }

    /**
     * If logging enabled, assemble meaningful log data and log to zendesk logger
     *
     * @param string $method
     * @param string $endpoint
     * @param array $requestData
     */
    protected function logRequestData($method, $endpoint, $requestData = [])
    {
        if (!$this->configHelper->getDebugLoggingEnabled()) {
            return; // Nothing to do here.
        }

        $debugData = [
            'method' => $method,
            'url' => $this->getApiUrl() . $this->getApiBasePath() . $endpoint
        ];

        if (!empty($this->getHeaders())) {
            $debugData['headers'] = $this->getHeaders();
        }

        if (!empty($requestData)) {
            $debugData['data'] = json_encode($requestData);
        }

        if ($this->getDebug()->lastResponseError instanceof \Exception) {
            $debugData['error_message'] = $this->getDebug()->lastResponseError->getMessage();
        }

        $this->logger->debug(print_r($debugData, true));
    }

    /**
     * {@inheritdoc}
     */
    public function get($endpoint, $queryParams = [])
    {
        try {
            $return = parent::get($endpoint, $queryParams);

            $this->logRequestData('get', $endpoint, $queryParams);

            return $return;
        } catch (\Zendesk\API\Exceptions\AuthException $ae) {
            $this->logRequestData('get', $endpoint, $queryParams);

            throw $ae;
        } catch (\Zendesk\API\Exceptions\ApiResponseException $are) {
            $this->logRequestData('get', $endpoint, $queryParams);

            throw $are;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function post($endpoint, $postData = [], $options = [])
    {
        try {
            $return = parent::post($endpoint, $postData, $options);

            $this->logRequestData('post', $endpoint, $postData);

            return $return;
        } catch (\Zendesk\API\Exceptions\AuthException $ae) {
            $this->logRequestData('post', $endpoint, $postData);

            throw $ae;
        } catch (\Zendesk\API\Exceptions\ApiResponseException $are) {
            $this->logRequestData('post', $endpoint, $postData);

            throw $are;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function put($endpoint, $putData = [])
    {
        try {
            $return = parent::put($endpoint, $putData);

            $this->logRequestData('put', $endpoint, $putData);

            return $return;
        } catch (\Zendesk\API\Exceptions\AuthException $ae) {
            $this->logRequestData('put', $endpoint, $putData);

            throw $ae;
        } catch (\Zendesk\API\Exceptions\ApiResponseException $are) {
            $this->logRequestData('put', $endpoint, $putData);

            throw $are;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($endpoint)
    {
        try {
            $return = parent::delete($endpoint);

            $this->logRequestData('delete', $endpoint);

            return $return;
        } catch (\Zendesk\API\Exceptions\AuthException $ae) {
            $this->logRequestData('delete', $endpoint);

            throw $ae;
        } catch (\Zendesk\API\Exceptions\ApiResponseException $are) {
            $this->logRequestData('delete', $endpoint);

            throw $are;
        }
    }

    /**
     * Set certain resources to subclass
     *
     * @return array
     */
    public static function getValidSubResources()
    {
        $resources = parent::getValidSubResources();

        $resources['brands'] = \Zendesk\Zendesk\ZendeskApi\Core\Brands::class;
        $resources['apps'] = \Zendesk\Zendesk\ZendeskApi\Core\Apps::class;

        return $resources;
    }
}
