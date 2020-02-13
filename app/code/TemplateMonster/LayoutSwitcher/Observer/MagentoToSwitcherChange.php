<?php

namespace TemplateMonster\LayoutSwitcher\Observer;

use Magento\Config\Model\Config\Factory as ConfigFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\Website;

/**
 * Class MagentoToSwitcherChange
 *
 * @package TemplateMonster\LayoutSwitcher\Observer
 */
class MagentoToSwitcherChange implements ObserverInterface
{
    /**
     * @var ConfigFactory
     */
    protected $_configFactory;

    /**
     * ChangeDefaultWebsite constructor.
     *
     * @param ConfigFactory $configFactory
     */
    public function __construct(ConfigFactory $configFactory)
    {
        $this->_configFactory = $configFactory;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        /** @var Website $website */
        $website = $observer->getData('data_object');

        if ($website->getIsDefault()) {
            $config = $this->_configFactory->create([
                'data' => $this->_getConfigData($website)
            ]);
            $config->save();
        }
    }

    /**
     * @param Website $website
     *
     * @return array
     */
    protected function _getConfigData(Website $website)
    {
        return [
            'section' => 'layout_switcher',
            'groups'  => [
                'general' => [
                    'fields' => [
                        'default_theme' => [
                            'value' => $website->getCode()
                        ],
                        'default_homepage' => [
                            'value' => $website->getDefaultStore()->getCode(),
                        ]
                    ]
                ]
            ]
        ];
    }
}