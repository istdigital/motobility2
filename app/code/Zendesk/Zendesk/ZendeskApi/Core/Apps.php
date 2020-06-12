<?php

namespace Zendesk\Zendesk\ZendeskApi\Core;

class Apps extends \Zendesk\API\Resources\Core\Apps
{
    /**
     * {@inheritdoc}
     */
    protected function setUpRoutes()
    {
        parent::setUpRoutes();

        $this->setRoutes([
            'getInstalledApps' => "{$this->resourceName}/installations.json",
            'remove' => "{$this->resourceName}/installations/{id}.json",
            'updateInstallation' => "{$this->resourceName}/installations/{id}.json"
        ]);
    }

    /**
     * Get all apps installed on zendesk support account
     *
     * @return \stdClass|null
     * @throws \Zendesk\API\Exceptions\ApiResponseException
     * @throws \Zendesk\API\Exceptions\AuthException
     */
    public function getInstalledApps()
    {
        return $this->client->get($this->getRoute(__FUNCTION__));
    }

    /**
     * Remove app from zendesk support
     *
     * @param $id
     * @return |null
     * @throws \Zendesk\API\Exceptions\ApiResponseException
     * @throws \Zendesk\API\Exceptions\AuthException
     */
    public function remove($id)
    {
        return $this->client->delete($this->getRoute(__FUNCTION__, ['id' => $id]));
    }

    /**
     * Update settings of an installed app
     *
     * @param int $id
     * @param array $params
     * @return \stdClass|null
     * @throws \Zendesk\API\Exceptions\ApiResponseException
     * @throws \Zendesk\API\Exceptions\AuthException
     */
    public function updateInstallation($id, array $params)
    {
        return $this->client->put($this->getRoute(__FUNCTION__, ['id' => $id]), $params);
    }
}
