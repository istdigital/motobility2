<?php

namespace Zendesk\Zendesk\ZendeskApi\Core;

class Brands extends \Zendesk\API\Resources\Core\Brands
{
    /**
     * Add routes specific to this subclass
     *
     * {@inheritdoc}
     */
    protected function setUpRoutes()
    {
        parent::setUpRoutes();

        $this->setRoutes([
            'getBrands' => "{$this->resourceName}.json"
        ]);
    }

    /**
     * Get brands available to current agent
     *
     * @return \stdClass|null
     * @throws \Zendesk\API\Exceptions\ApiResponseException
     * @throws \Zendesk\API\Exceptions\AuthException
     */
    public function getBrands()
    {
        return $this->client->get($this->getRoute(__FUNCTION__));
    }
}
