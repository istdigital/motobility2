<?php

namespace Zendesk\Zendesk\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements \Magento\Framework\Setup\InstallDataInterface
{
    /**
     * @var \Zendesk\Zendesk\Helper\Integration
     */
    protected $integrationHelper;

    /**
     * InstallData constructor.
     * @param \Zendesk\Zendesk\Helper\Integration $integrationHelper
     */
    public function __construct(
        \Zendesk\Zendesk\Helper\Integration $integrationHelper
    ) {
        $this->integrationHelper = $integrationHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->integrationHelper->createIntegration();
    }
}
