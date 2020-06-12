<?php

namespace Zendesk\Zendesk\Model;

class CorsRepository implements \Zendesk\Zendesk\Api\CorsRepositoryInterface
{

    /**
     * {@inheritdoc}
     */
    public function accessControlAllowOrigin()
    {
        return ''; // Any response is sufficient
    }
}
