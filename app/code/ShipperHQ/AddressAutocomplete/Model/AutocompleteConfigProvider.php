<?php
/**
 *
 * ShipperHQ
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * Shipper HQ Shipping
 *
 * @category  ShipperHQ
 * @package   ShipperHQ_Address_Autocomplete
 * @copyright Copyright (c) 2017 Zowta LLC (http://www.ShipperHQ.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    ShipperHQ Team sales@shipperhq.com
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShipperHQ\AddressAutocomplete\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class AutocompleteConfigProvider implements ConfigProviderInterface {
	/**
	 * @var ScopeConfigInterface
	 */
	private $scopeConfig;

	public function __construct(
		ScopeConfigInterface $scopeConfig
	) {

		$this->scopeConfig = $scopeConfig;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getConfig() {
		$config['shipperhq_autocomplete'] = [
			'active' => $this->getConfigValue('shipping/shipper_autocomplete/active'),
			'api_key' => $this->getConfigValue('shipping/shipper_autocomplete/google_api_key'),
			'use_geolocation' => $this->getConfigValue('shipping/shipper_autocomplete/use_geolocation'),
			'use_long_postcode' => $this->getConfigValue('shipping/shipper_autocomplete/use_long_postcode'),
		];
		return $config;
	}

	private function getConfigValue($path, $storeId = 0) {
		return $this->scopeConfig->getValue(
			$path,
			ScopeInterface::SCOPE_STORE,
			$storeId
		);
	}
}
