<?php

namespace TemplateMonster\SocialLogin\Model\Provider;

use TemplateMonster\SocialLogin\Model\Provider;

/**
 * Google OAuth service provider implementation.
 */
class Google extends Provider
{
    /**
     * {@inheritdoc}
     */
    protected $authorizationUrl = 'https://accounts.google.com/o/oauth2/auth';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://accounts.google.com/o/oauth2/token';

    /**
     * {@inheritdoc}
     */
    protected $userDataUrl = 'https://www.googleapis.com/oauth2/v1/userinfo';

    /**
     * {@inheritdoc}
     */
    protected $normalizedFields = array(
        'id' => 'provider_id',
        'given_name' => 'firstname',
        'family_name' => 'lastname',
        'email' => 'email',
    );

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return 'google';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Google';
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationUrlParams()
    {
        return array_merge(
            parent::getAuthorizationUrlParams(),
            array(
                'scope' => 'https://www.googleapis.com/auth/userinfo.email',
                'response_type' => 'code',
            )
        );
    }
}
