<?php

namespace Zendesk\Zendesk\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;

class Integration extends \Magento\Framework\App\Helper\AbstractHelper
{
    const INTEGRATION_NAME = 'Zendesk';

    const INTEGRATION_RESOURCES = [
        'Zendesk_Zendesk::zendesk',
        'Magento_Customer::customer',
        'Magento_Sales::sales',
        'Magento_Sales::actions_view'
    ];

    /**
     * @var \Magento\Integration\Api\IntegrationServiceInterface
     */
    protected $integrationService;

    /**
     * @var \Magento\Integration\Model\Integration|null
     */
    protected $integration;
    /**
     * @var \Magento\Integration\Api\OauthServiceInterface
     */
    protected $oauthService;

    /**
     * Integration constructor.
     * @param Context $context
     * @param \Magento\Integration\Api\IntegrationServiceInterface $integrationService
     * @param \Magento\Integration\Api\OauthServiceInterface $oauthService
     */
    public function __construct(
        Context $context,
        // End parent parameters
        \Magento\Integration\Api\IntegrationServiceInterface $integrationService,
        \Magento\Integration\Api\OauthServiceInterface $oauthService
    ) {
        parent::__construct($context);
        $this->integrationService = $integrationService;
        $this->oauthService = $oauthService;
    }

    /**
     * Get Zendesk integration instance
     *
     * @return \Magento\Integration\Model\Integration
     * @throws NoSuchEntityException
     */
    public function getIntegration()
    {
        if ($this->integration === null) {
            $integration = $this->integrationService->findByName(self::INTEGRATION_NAME);

            if (empty($integration->getId())) {
                throw new NoSuchEntityException(__('Zendesk integration in Magento is not configured.'));
            }

            $this->integration = $integration;
        }
        return $this->integration;
    }

    /**
     * Get Zendesk integration auth token
     *
     * @return string
     * @throws NoSuchEntityException
     * @throws IntegrationException
     */
    public function getAuthToken()
    {
        $integration = $this->getIntegration();

        $token = $this->oauthService->getAccessToken($integration->getConsumerId())->getToken();

        if (empty($token)) {
            throw new IntegrationException(__('Unable to get Zendesk integration auth token.'));
        }

        return $token;
    }

    /**
     * Get Zendesk integration data array
     *
     * @return array
     */
    public function getIntegrationData()
    {
        return [
            'name' => self::INTEGRATION_NAME,
            'status' => \Magento\Integration\Model\Integration::STATUS_INACTIVE,
            'all_resources' => false,
            'resource' => self::INTEGRATION_RESOURCES
        ];
    }

    /**
     * Create integration if it doesn't already exist.
     *
     * @throws IntegrationException
     */
    public function createIntegration()
    {
        try {
            $integration = $this->getIntegration();

            return; // Integration already exists -- nothing to do.
        } catch (NoSuchEntityException $e) {
            // Intentionally swallow exception and allow process to continue.
        }

        $integration = $this->integrationService->create($this->getIntegrationData());

        if ($this->oauthService->createAccessToken($integration->getConsumerId())) {
            $integration->setStatus(\Magento\Integration\Model\Integration::STATUS_ACTIVE)->save();
        }
    }

    /**
     * Remove integration, if it exists.
     *
     * @throws IntegrationException
     */
    public function removeIntegration()
    {
        try {
            $integration = $this->getIntegration();

            $this->integrationService->delete($integration->getId());
        } catch (NoSuchEntityException $e) {
            return; // Integration doesn't exist -- nothing to do here.
        }
    }
}
