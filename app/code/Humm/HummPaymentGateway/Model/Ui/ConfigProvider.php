<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Humm\HummPaymentGateway\Model\Ui;

use Humm\HummPaymentGateway\Gateway\Config\Config;
use Magento\Backend\Model\Session\Quote;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Asset\Repository;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface {
    const LAUNCH_TIME_URL = 'https://s3-ap-southeast-2.amazonaws.com/humm-variables/launch-time.txt';
    const LAUNCH_TIME_DEFAULT = "2019-04-07 14:30:00 UTC";
    const LAUNCH_TIME_CHECK_ENDS = "2019-10-07 13:30:00 UTC";

    protected $_gatewayConfig;
    protected $_scopeConfigInterface;
    protected $customerSession;
    protected $_urlBuilder;
    protected $request;
    protected $_assetRepo;
    protected $_resourceConfig;

    public function __construct(
        Config $gatewayConfig,
        Session $customerSession,
        Quote $sessionQuote,
        Context $context,
        Repository $assetRepo,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig
    ) {
        $this->_gatewayConfig        = $gatewayConfig;
        $this->_scopeConfigInterface = $context->getScopeConfig();
        $this->customerSession       = $customerSession;
        $this->sessionQuote          = $sessionQuote;
        $this->_urlBuilder           = $context->getUrlBuilder();
        $this->_assetRepo            = $assetRepo;
        $this->_resourceConfig       = $resourceConfig;
    }

    private function updateLaunchDate() {
        if ( time() - strtotime( self::LAUNCH_TIME_CHECK_ENDS ) > 0 ) {
            // if after LAUNCH_TIME_CHECK_ENDS time, and launch_time is still empty, set it to default launch time, and done.
            if ( ! $this->_gatewayConfig->getLaunchTime() ) {
                $this->_resourceConfig->saveConfig( 'payment/humm_gateway/launch_time', strtotime( self::LAUNCH_TIME_DEFAULT ), 'default', 0 );
            }

            return;
        }
        $launch_time             = $this->_gatewayConfig->getLaunchTime();
        $launch_time_update_time = $this->_gatewayConfig->getLaunchTimeUpdated();
        if ( empty( $launch_time ) || empty( $launch_time_update_time ) || ( time() - $launch_time_update_time >= 3600 ) ) {
            $remote_launch_time_string = '';
            try {
                $remote_launch_time_string = file_get_contents( self::LAUNCH_TIME_URL );
            } catch ( \Exception $exception ) {
            }
            if ( ! empty( $remote_launch_time_string ) ) {
                $launch_time = strtotime( $remote_launch_time_string );
                $this->_resourceConfig->saveConfig( 'payment/humm_gateway/launch_time', $launch_time, 'default', 0 );
                $this->_resourceConfig->saveConfig( 'payment/humm_gateway/launch_time_updated', time(), 'default', 0 );
            } elseif ( empty( $launch_time ) || ( empty( $launch_time_update_time ) && $launch_time != strtotime( self::LAUNCH_TIME_DEFAULT ) ) ) {
                // this is when $launch_time_string never set (first time run of the plugin), or local const LAUNCH_TIME_DEFAULT changes and and never update from remote.
                // Mainly for development, for changing const LAUNCH_TIME_DEFAULT to take effect.
                // if $launch_time has been updated later by remote, then changing self::LAUNCH_TIME_DEFAULT should not affect $launch_time
                $launch_time = strtotime( self::LAUNCH_TIME_DEFAULT );
                $this->_resourceConfig->saveConfig( 'payment/humm_gateway/launch_time', $launch_time, 'default', 0 );
            }
        }
    }

    public function getConfig() {
        $this->updateLaunchDate();
        $logoFile = $this->_gatewayConfig->getLogo();

        /** @var $om \Magento\Framework\ObjectManagerInterface */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var $request \Magento\Framework\App\RequestInterface */
        $request = $om->get( 'Magento\Framework\App\RequestInterface' );
        $params  = array();
        $params  = array_merge( [ '_secure' => $request->isSecure() ], $params );
        $logo    = $this->_assetRepo->getUrlWithParams( 'Humm_HummPaymentGateway::images/' . $logoFile, $params );

        $config = [
            'payment' => [
                Config::CODE => [
                    'title'             => $this->_gatewayConfig->getTitle(),
                    'description'       => $this->_gatewayConfig->getDescription(),
                    'logo'              => $logo,
                    'allowed_countries' => $this->_gatewayConfig->getSpecificCountry(),
                ]
            ]
        ];

        return $config;
    }
}
