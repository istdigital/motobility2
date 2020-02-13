<?php

namespace TemplateMonster\SocialLogin\Model\Provider;

use TemplateMonster\SocialLogin\Model\Exception;
use TemplateMonster\SocialLogin\Model\Provider;
use Magento\Framework\HTTP\ZendClient;

/**
 * Twitter OAuth service provider implementation.
 */
class Twitter extends Provider
{
    /**
     * Request token url.
     *
     * @var string
     */
    protected $requestTokenUrl = 'https://api.twitter.com/oauth/request_token';

    /**
     * {@inheritdoc}
     */
    protected $authorizationUrl = 'https://api.twitter.com/oauth/authenticate';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://api.twitter.com/oauth/access_token';

    /**
     * {@inheritdoc}
     */
    protected $userDataUrl = 'https://api.twitter.com/1.1/account/verify_credentials.json';

    /**
     * Session request token key for twitter.
     */
    const SESSION_REQUEST_TOKEN = 'twitter-request-token';

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return 'twitter';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Twitter';
    }

    /**
     * {@inheritdoc}
     */
    public function isHasMissingData()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationUrlParams()
    {
        return [
            'oauth_token' => $this->getRequestToken(true)['oauth_token'],
        ];
    }

    /**
     * Get request token.
     *
     * @param bool $refresh
     *
     * @return mixed
     */
    protected function getRequestToken($refresh = false)
    {
        if (true == $refresh) {
            $url = $this->getRequestTokenUrl();
            $params = $this->getRequestTokenUrlParams();
            $response = $this->getResponse($url, $params, ZendClient::POST);
            $token = $this->parseResponse($response);
            $this->checkToken($token);

            $this->customerSession->setData(self::SESSION_REQUEST_TOKEN, $token);
        }

        return $this->customerSession->getData(self::SESSION_REQUEST_TOKEN);
    }

    /**
     * Get request token url.
     *
     * @return string
     */
    protected function getRequestTokenUrl()
    {
        return $this->requestTokenUrl;
    }

    /**
     * Get request token url params.
     */
    protected function getRequestTokenUrlParams()
    {
        $params = $this->getCommonOAuthParams();

        $params['oauth_signature'] = $this->httpUtility->sign(
            $params,
            'HMAC-SHA1',
            $this->options['client_secret'],
            '',
            'POST',
            $this->requestTokenUrl
        );

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAccessTokenUrlParams()
    {
        $params = array_merge(
            $this->getCommonOAuthParams(),
            [
                'oauth_token' => $this->getRequestToken()['oauth_token'],
                'oauth_verifier' => $this->request->getParam('oauth_verifier'),
            ]
        );

        $params['oauth_signature'] = $this->httpUtility->sign(
            $params,
            'HMAC-SHA1',
            $this->options['client_secret'],
            '',
            'POST',
            $this->accessTokenUrl
        );

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkToken(array $token)
    {
        if (empty($token['oauth_token'])) {
            throw new Exception(
                'Invalid or missing oauth token.',
                Exception::TYPE_MISSING_TOKEN
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserDataUrlParams(array $token)
    {
        $params = array_merge(
            $this->getCommonOAuthParams(),
            [
                'oauth_token' => $token['oauth_token'],
            ]
        );

        $params['oauth_signature'] = $this->httpUtility->sign(
            $params,
            'HMAC-SHA1',
            $this->options['client_secret'],
            $token['oauth_token_secret'],
            'GET',
            $this->userDataUrl
        );

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData(array $data)
    {
        $fullName = $data['name'];

        // Trying to guess first name & last name based on full name
        $spacePosition = mb_strpos($fullName, ' ');

        if (false !== $spacePosition) {
            $firstName = mb_substr($fullName, 0, $spacePosition);
            $lastName = mb_substr($fullName, $spacePosition + 1);
        } else {
            $firstName = $fullName;
            $lastName = $fullName;
        }

        return [
            'firstname' => $firstName,
            'lastname' => $lastName ?: $firstName,
            'email' => $data['screen_name'].'@twitter.com',
            'provider_code' => $this->getCode(),
            'provider_id' => $data['id'],
        ];
    }

    /**
     * Get common OAuth1a params.
     *
     * @return array]
     */
    protected function getCommonOAuthParams()
    {
        return [
            'oauth_consumer_key' => $this->options['client_id'],
            'oauth_timestamp' => $this->httpUtility->generateTimestamp(),
            'oauth_nonce' => $this->httpUtility->generateNonce(),
            'oauth_version' => '1.0',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_callback' => $this->getRedirectUri(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getResponse($url, $params = [], $method = ZendClient::GET)
    {
        foreach ($params as $key => $value) {
            $params[$key] = sprintf('%s="%s"', $key, rawurlencode($value));
        }

        $httpClient = $this->httpClient;
        $httpClient
            ->resetParameters(true)
            ->setHeaders('Authorization', 'OAuth '.implode(', ', $params))
            ->setUri($url)
            ->setMethod($method)
        ;

        return $httpClient->request()->getBody();
    }
}
