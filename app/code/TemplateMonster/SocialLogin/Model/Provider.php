<?php

namespace TemplateMonster\SocialLogin\Model;

use TemplateMonster\SocialLogin\Helper\Data as SocialLoginHelper;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;

/**
 * Abstract class for OAuth providers.
 */
abstract class Provider extends DataObject implements ProviderInterface
{
    /**
     * OAuth authorization url.
     *
     * @var string
     */
    protected $authorizationUrl;

    /**
     * OAuth access token url.
     *
     * @var string
     */
    protected $accessTokenUrl;

    /**
     * OAuth user data url.
     *
     * @var string
     */
    protected $userDataUrl;

    /**
     * Normalized urls.
     *
     * @var array
     */
    protected $normalizedFields = [];

    /**
     * Provider options.
     *
     * @var array
     */
    protected $options = [
        'enabled' => false,
        'client_id' => null,
        'client_secret' => null,
        'sort_order' => 0,
    ];

    /**
     * @var SocialLoginHelper
     */
    protected $helper;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var ZendClient
     */
    protected $httpClient;

    /**
     * @var \Zend_Oauth_Http_Utility
     */
    protected $httpUtility;

    /**
     * Session state key.
     */
    const SESSION_STATE_KEY = '%s-state';

    public function __construct(
        SocialLoginHelper $helper,
        CustomerSession $customerSession,
        RequestInterface $request,
        JsonHelper $jsonHelper,
        ZendClient $httpClient,
        \Zend_Oauth_Http_Utility $httpUtility = null,
        array $options = []
    ) {
        $this->resolveOptions($options);

        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->jsonHelper = $jsonHelper;
        $this->httpClient = $httpClient;
        $this->httpUtility = $httpUtility ?: new \Zend_Oauth_Http_Utility();
        $this->options = array_merge($this->options, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return (int) $this->options['sort_order'];
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return (bool) $this->options['enabled'];
    }

    /**
     * {@inheritdoc}
     */
    public function isHasMissingData()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationUrl()
    {
        return sprintf('%s?%s',
            $this->authorizationUrl,
            http_build_query($this->getAuthorizationUrlParams())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken()
    {
        $url = $this->getAccessTokenUrl();
        $params = $this->getAccessTokenUrlParams();
        $response = $this->getResponse($url, $params, ZendClient::POST);
        $token = $this->parseResponse($response);

        $this->checkToken($token);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserData($token)
    {
        $url = $this->getUserDataUrl();
        $params = $this->getUserDataUrlParams($token);
        $response = $this->getResponse($url, $params);
        $data = $this->parseResponse($response);
        $normalized = $this->normalizeData($data);

        $this->checkUserData($normalized);

        return $normalized;
    }

    /**
     * Build params query string for authorization url.
     *
     * @return array
     */
    protected function getAuthorizationUrlParams()
    {
        return [
            'response_type' => 'code',
            'client_id' => $this->options['client_id'],
            'redirect_uri' => $this->getRedirectUri(),
            'state' => $this->generateAndRememberState(),
        ];
    }

    /**
     * Get access token url.
     *
     * @return string
     */
    protected function getAccessTokenUrl()
    {
        return $this->accessTokenUrl;
    }

    /**
     * Get access url url params.
     *
     * @return array
     */
    protected function getAccessTokenUrlParams()
    {
        return [
            'grant_type' => 'authorization_code',
            'client_id' => $this->options['client_id'],
            'client_secret' => $this->options['client_secret'],
            'code' => $this->getAuthorizationCode(),
            'redirect_uri' => $this->getRedirectUri(),
        ];
    }

    /**
     * Check access token.
     *
     * @param array $token
     *
     * @throws Exception
     */
    protected function checkToken(array $token)
    {
        if (empty($token['access_token'])) {
            throw new Exception(
                'Invalid or missing oauth token.',
                Exception::TYPE_MISSING_TOKEN
            );
        }
    }

    /**
     * Get user data.
     *
     * @return string
     */
    protected function getUserDataUrl()
    {
        return $this->userDataUrl;
    }

    /**
     * Get user data url params.
     *
     * @param array $token
     *
     * @return array
     */
    protected function getUserDataUrlParams(array $token)
    {
        return [
            'client_id' => $this->options['client_id'],
            'client_secret' => $this->options['client_secret'],
            'access_token' => $token['access_token'],
        ];
    }

    /**
     * Get response by url.
     *
     * @param string $url
     * @param array  $params
     * @param string $method
     *
     * @return string
     */
    protected function getResponse($url, $params = [], $method = ZendClient::GET)
    {
        $httpClient = $this->httpClient;
        $httpClient
            ->resetParameters(true)
            ->setUri($url)
            ->setMethod($method)
        ;
        $method == ZendClient::POST ? $httpClient->setParameterPost($params) : $httpClient->setParameterGet($params);

        return $httpClient->request()->getBody();
    }

    /**
     * Parse response.
     *
     * @param string $content
     *
     * @return array|mixed
     */
    protected function parseResponse($content)
    {
        if (empty($content)) {
            return [];
        }

        // assume response in JSON, fallback to key=value pairs otherwise
        try {
            $response = $this->jsonHelper->jsonDecode($content);
        } catch (\Exception $e) {
            parse_str($content, $response);
        }

        return $response;
    }

    /**
     * Resolve options.
     *
     * @param array $options
     */
    protected function resolveOptions(array $options)
    {
        if (isset($options['enabled']) && $options['enabled']) {
            assert(!empty($options['client_id']), sprintf('Missing configuration option %s client id.', $this->getName()));
            assert(!empty($options['client_secret']), sprintf('Missing configuration option Facebook client secret.', $this->getName()));
        }
    }

    /**
     * Normalize data.
     *
     * @param array $data
     *
     * @return array
     */
    protected function normalizeData(array $data)
    {
        $normalized = [];
        foreach ($this->normalizedFields as $before => $after) {
            $normalized[$after] = $data[$before];
        }
        $normalized['provider_code'] = $this->getCode();

        return $normalized;
    }

    /**
     * Check user data.
     *
     * @param array $data
     *
     * @throws Exception
     */
    protected function checkUserData(array $data)
    {
        foreach (['email', 'provider_id'] as $field) {
            if (empty($data[$field])) {
                throw new Exception(
                    "Field «{$field}» is missing on your {$this->getName()} account.",
                    Exception::TYPE_INVALID_USER_DATA
                );
            }
        }
    }

    /**
     * Get redirect uri.
     *
     * @return string
     */
    protected function getRedirectUri()
    {
        return $this->helper->getRedirectUri($this);
    }

    /**
     * Generate and remember in session un guessable state.
     *
     * @return string
     */
    protected function generateAndRememberState()
    {
        $nonce = $this->httpUtility->generateNonce();
        $this->customerSession->setData(
            sprintf(self::SESSION_STATE_KEY, $this->getCode()),
            $nonce
        );

        return $nonce;
    }

    /**
     * Generate state.
     *
     * @return string
     */
    protected function generateState()
    {
        return md5(microtime(true).uniqid('', true));
    }

    /**
     * Get authorization code.
     *
     * @return mixed
     *
     * @throws Exception
     */
    protected function getAuthorizationCode()
    {
        $sessionState = $this->customerSession->getData(
            sprintf(self::SESSION_STATE_KEY, $this->getCode()),
            true
        );
        $requestState = $this->request->getParam('state');

        if ($sessionState !== $requestState) {
            throw new Exception(
                'Authorization state mismatch.',
                Exception::TYPE_AUTHORIZATION_STATE_MISMATCH
            );
        }

        return $this->request->getParam('code');
    }
}
