<?php

namespace TemplateMonster\SocialLogin\Model;

/**
 * Base module exception.
 */
class Exception extends \Exception
{
    /**
     * Provider not found exception type.
     */
    const TYPE_PROVIDER_NOT_FOUND = 0;

    /**
     * Authorization state mismatch type.
     */
    const TYPE_AUTHORIZATION_STATE_MISMATCH = 1;

    /**
     * Missing token exception type.
     */
    const TYPE_MISSING_TOKEN = 2;

    /**
     * Invalid user data exception type.
     */
    const TYPE_INVALID_USER_DATA = 3;
}
