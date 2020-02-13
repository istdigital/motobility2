<?php

namespace TemplateMonster\SocialLogin\Model\Provider;

use TemplateMonster\SocialLogin\Model\Provider;

/**
 * Facebook OAuth service provider implementation.
 */
class Facebook extends Provider
{
    /**
     * {@inheritdoc}
     */
    protected $authorizationUrl = 'https://www.facebook.com/v2.0/dialog/oauth';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://graph.facebook.com/v2.0/oauth/access_token';

    /**
     * {@inheritdoc}
     */
    protected $userDataUrl = 'https://graph.facebook.com/v2.0/me';

    /**
     * {@inheritdoc}
     */
    protected $normalizedFields = array(
        'id' => 'provider_id',
        'first_name' => 'firstname',
        'last_name' => 'lastname',
        'email' => 'email',
    );

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return 'facebook';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Facebook';
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationUrlParams()
    {
        return array_merge(
            parent::getAuthorizationUrlParams(),
            array(
                'scope' => 'email',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserDataUrlParams(array $token)
    {
        return array_merge(
            parent::getUserDataUrlParams($token),
            array(
                'fields' => 'id,name,email,first_name,last_name',
            )
        );
    }
}
