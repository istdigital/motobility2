<?php

namespace Zendesk\Zendesk\Api;

interface CorsRepositoryInterface
{
    /**
     * Simple repository method to add Access-Control-Allow-Origin headers
     * to permit calls to API via ajax from Zendesk.
     *
     * @api
     * @return string
     */
    public function accessControlAllowOrigin();
}
