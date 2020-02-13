<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
namespace Manadev\Core\Observers;

use Magento\Backend\App\AbstractAction;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Manadev\Core\Features;
use Magento\Config\Model\ResourceModel\Config;
use Manadev\Core\Resources\ExtensionCollectionFactory;

class UpdateVersionNumbers implements ObserverInterface
{
    /**
     * @var Features
     */
    protected $features;
    /**
     * @var ReinitableConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var Config
     */
    protected $resourceConfig;
    /**
     * @var ExtensionCollectionFactory
     */
    protected $collectionFactory;

    public function __construct(Features $features, ReinitableConfigInterface $scopeConfig, Config $resourceConfig,
        ExtensionCollectionFactory $collectionFactory) {
        $this->features = $features;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConfig = $resourceConfig;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer) {
        if(! ($observer->getData('controller_action') instanceof AbstractAction)) {
            return;
        }

        $date = $this->scopeConfig->getValue('manadev/updated_at');
        if ($date && time() - strtotime($date) < 60 * 60 * 24 * 30) {
            return;
        }

        try {
            $versions = [];
            $collection = $this->collectionFactory->create();
            $collection->setStore(0);
            foreach ($collection as $extension) {
                if (!$extension->getData('version') || !$extension->getData('code')) {
                    continue;
                }
                $versions[$extension->getData('code')] = $extension->getData('version');
            }

            $this->features->updateVersionInfo($versions);
        }
        catch (\Exception $e) {
        }

        $this->resourceConfig->saveConfig('manadev/updated_at', date('Y-m-d'), 'default', 0);
        $this->scopeConfig->reinit();
    }
}