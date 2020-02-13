<?php

namespace TemplateMonster\SocialLogin\Model\Provider;

use TemplateMonster\SocialLogin\Model\Provider;

/**
 * Instagram OAuth service provider implementation.
 */
class Instagram extends Provider
{
    /**
     * {@inheritdoc}
     */
    protected $authorizationUrl = 'https://api.instagram.com/oauth/authorize';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://api.instagram.com/oauth/access_token';

    /**
     * {@inheritdoc}
     */
    protected $userDataUrl = 'https://api.instagram.com/v1/users/self';

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return 'instagram';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Instagram';
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
        return array_merge(
            parent::getAuthorizationUrlParams(),
            [
                'scope' => 'basic',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData(array $data)
    {
        $data = $data['data'];

        $fullName = $data['full_name'];

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
            'lastname' => $lastName,
            'email' => $data['username'].'@instagram.com',
            'provider_code' => $this->getCode(),
            'provider_id' => $data['id'],
        ];
    }
}
