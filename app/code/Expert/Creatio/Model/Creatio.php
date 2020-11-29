<?php

namespace Expert\Creatio\Model;

use Magento\Framework\App\Helper\Context;

class Creatio {
	//private $auth_url = "https://blueskyhealthcare.creatio.com/";
	private $auth_url = null;
	private $select_url = "0/dataservice/json/reply/SelectQuery";
	private $insert_url = "0/dataservice/json/reply/InsertQuery";
	private $update_url = "0/dataservice/json/reply/UpdateQuery";
	private $auth_username = null;
	private $auth_password = null;
	private $cookie = "cookies.txt";
	private $_csrf = null;
	private $logger = null;

	/**
	 * Data constructor.
	 *
	 * @param ColorScheme $colorScheme
	 * @param Context $context
	 */
	public function __construct($auth_url, $auth_username, $auth_password) {
		$this->auth_url = $auth_url;
		$this->auth_username = $auth_username;
		$this->auth_password = $auth_password;

		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/crm.log');
		$this->logger = new \Zend\Log\Logger();
		$this->logger->addWriter($writer);

		$this->login();
	}

	function login() {
		if (!file_exists($this->cookie)) {
			$fh = fopen($this->cookie, "w");
			fwrite($fh, "");
			fclose($fh);
		}
		$postdata = json_encode([
			'UserName' => $this->auth_username,
			'UserPassword' => $this->auth_password,
		]);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->auth_url . "ServiceModel/AuthService.svc/Login");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie); // Cookie aware
		curl_setopt($ch, CURLOPT_COOKIEJAR, realpath($this->cookie)); // Cookie aware
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($postdata))
		);
		$response = json_decode(curl_exec($ch), true);
		curl_close($ch);
		if ($response['Code'] == 0) {
			$cookie = file_get_contents($this->cookie);
			preg_match("/BPMCSRF.*/", $cookie, $_csrf);
			if (count($_csrf)) {
				$this->_csrf = trim(str_replace("BPMCSRF", '', $_csrf[0]));
			}
		} else {
			$this->logger->info($response['Message']);
			echo $response['Message'];
			//throw new \Exception("Creatio:Invalid Credentials", 1);
		}
	}

	function request($url, $data) {
		if (!$this->_csrf) {
			return false;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_COOKIEFILE, realpath($this->cookie)); // Cookie aware
		curl_setopt($ch, CURLOPT_COOKIEJAR, realpath($this->cookie)); // Cookie aware
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'BPMCSRF: ' . $this->_csrf,
			'Content-Length: ' . strlen($data))
		);
		$response = json_decode(curl_exec($ch), true);
		curl_close($ch);
		if (!$response['success']) {
			$this->logger->info($response['responseStatus']['Message']);
			$this->logger->info(print_r($data, true));
		}
		return $response;
	}

	function getOptions($type) {
		$postdata = json_encode([
			'RootSchemaName' => $type,
			'OperationType' => 0,
			'IsPageable' => false,
			'AllColumns' => true,
			'RowCount' => -1,
		]);

		$response = $this->request($this->auth_url . $this->select_url, $postdata);

		if ($response && $response['success']) {
			$r = [];
			foreach ($response['rows'] as $ele) {
				$r[] = ['value' => $ele['Id'], 'label' => $ele['Name']];
			}
			return $r;
		} else {
			return [];
		}
	}

	function updateContact($_data) {
		$filters = $this->getFilter('Contact', 'Email', $_data['Email']);
		$data = $this->formatInsertData('Contact', $_data, 2, $filters);
		$response = $this->request($this->auth_url . $this->update_url, $data);
		if ($response['success']) {
			return true;
		} else {
			return false;
		}

	}

	function getFilter($type, $column, $value) {
		return [
			"RootSchemaName" => $type,
			"ComparisonType" => 3,
			"FilterType" => 1, //CompareFilter
			"isEnabled" => true, //Enable
			"LeftExpression" => [
				'expressionType' => 0, //SchemaColumn
				'columnPath' => $column,
				'parameter' => [
					//'dataValueType' => 0,
					'value' => $column,
				],
			],
			"RightExpression" => [
				'expressionType' => 2, //Parameter
				'parameter' => [
					//'dataValueType' => 0,
					'value' => $value,
				],
			],
		];
	}

	function getContact($email) {
		$data = json_encode([
			'RootSchemaName' => 'Contact',
			'OperationType' => 0,
			'IsPageable' => false,
			'AllColumns' => false,
			'Filters' => $this->getFilter('Contact', 'Email', $email),
		]);

		$response = $this->request($this->auth_url . $this->select_url, $data);
		if ($response['success']) {
			return $response['rows'][0]['Id'] ?? false;
		} else {
			return false;
		}
	}

	function getProductIdyByCode($code) {
		$data = json_encode([
			'RootSchemaName' => 'Product',
			'OperationType' => 0,
			'IsPageable' => false,
			'AllColumns' => false,
			'Filters' => $this->getFilter('Product', 'Code', $code),
		]);
		$response = $this->request($this->auth_url . $this->select_url, $data);
		if ($response['success']) {
			return $response['rows'][0]['Id'] ?? false;
		} else {
			return false;
		}

	}

	function getOrderIdyByNumber($code) {
		$data = json_encode([
			'RootSchemaName' => 'Order',
			'OperationType' => 0,
			'IsPageable' => false,
			'AllColumns' => false,
			'Filters' => $this->getFilter('Order', 'Number', $code),
		]);
		$response = $this->request($this->auth_url . $this->select_url, $data);
		if ($response['success']) {
			return $response['rows'][0]['Id'] ?? false;
		} else {
			return false;
		}

	}

	function create($data, $type) {
		$data = $this->formatInsertData($type, $data);
		$response = $this->request($this->auth_url . $this->insert_url, $data);

		if ($response['success']) {
			return $response['id'];
		} else {
			return false;
		}

	}

	function formatInsertData($type, $data, $operation = 1, $filters = []) {
		$r = [
			'RootSchemaName' => $type,
			'OperationType' => $operation,
		];
		$cols = [];
		foreach ($data as $key => $value) {
			if (!empty($value)) {
				$datatype = 1;
				if (preg_match("/^[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}$/", $value)) {
					$datatype = 0;
				} else if (is_numeric($value) && !in_array($key, ['Phone', 'MobilePhone', 'Zip'])) {
					$datatype = 5; //FLOAT
				}

				$cols[$key] = [
					"ExpressionType" => 2,
					"Parameter" => [
						"DataValueType" => $datatype,
						"Value" => $value,
					],
				];
			}
		}
		$r['ColumnValues']['Items'] = $cols;

		if (count($filters)) {
			$r['Filters'] = $filters;
		}

		return json_encode($r);
	}

	function getCountryIdByCode($code) {
		$countries = [
			'96ae123c-f46b-1410-f998-00155d043204' => 'GBR',
			'e0be1264-f36b-1410-fa98-00155d043204' => 'USA',
			'a470b005-e8bb-df11-b00f-001d60e938c6' => 'UKR',
			'a570b005-e8bb-df11-b00f-001d60e938c6' => 'RUS',
			'a670b005-e8bb-df11-b00f-001d60e938c6' => 'AU',
			'a770b005-e8bb-df11-b00f-001d60e938c6' => 'KAZ',
			'6525ec6f-8650-45f8-915a-a61b6f3bf09f' => 'BLR',
			'4a88fe46-607b-4e74-ad4f-d80a50079ba6' => 'NZL',
		];
		$key = array_search($code, $countries);
		if ($key === FALSE) {
			return '';
		}

		return $key;
	}

	function getPaymentTypeByCode($code) {
		// $ptypes = [
		// 	'47d85c8e-82e0-47e2-b963-390542d4a360' =>	'afterpay',
		// 	'bf4f69c8-e242-4b0e-83c3-1c8d4790fc01' =>	'cryozonic_stripe', // paypal_express
		// 	'bf4f69c8-e242-4b0e-83c3-1c8d4790fc01' =>	'paypal_express',
		// 	'0026bde9-932b-4baa-ba30-ffa521a255ab' =>	'cashondelivery',
		// 	'7d5ded73-ce32-4de6-94ba-e58d2e6b3675' =>	'checkmo',
		// ];

		if (preg_match('/stripe|paypal/i', $code)) {
			return 'bf4f69c8-e242-4b0e-83c3-1c8d4790fc01';
		} else if (preg_match('/checkmo/i', $code)) {
			return '7d5ded73-ce32-4de6-94ba-e58d2e6b3675';
		} else if (preg_match('/cashondelivery/i', $code)) {
			return '0026bde9-932b-4baa-ba30-ffa521a255ab';
		} else if (preg_match('/afterpay/i', $code)) {
			return '47d85c8e-82e0-47e2-b963-390542d4a360';
		} else if (preg_match('/humm/i', $code)) {
			return '47d85c8e-82e0-47e2-b963-390542d4a360';
		} else {
			return '7d5ded73-ce32-4de6-94ba-e58d2e6b3675';
		}
	}

	function getDeliveryTypeByCode($code) {
		$countries = [
			'9f0a8bf8-4343-4966-b634-11757224de0e' => 'afterpay',
			'7f3e3aff-2d34-49a7-b4ed-411241ad59d9' => 'Stripe',
			'bf4f69c8-e242-4b0e-83c3-1c8d4790fc01' => 'paypal',
			'0026bde9-932b-4baa-ba30-ffa521a255ab' => 'cashondelivery',
		];
		$key = array_search($code, $countries);
		if ($key === FALSE) {
			return '';
		}

		return $key;
	}

	function getRegionIdByCode($code) {
		$regions = [
			'05b75c89-736e-43c8-8db6-4646fe69ef7f' => 569, // Australian Capital Territory
			'433a3379-b218-4662-bfb6-44ce48c64c8a' => 570, // New South Wales
			'8ba82b5c-8182-4e70-9b82-69e315856bb8' => 571, // Victoria
			'd227fc10-f294-4977-866e-15c1075d688e' => 572, // Queensland
			'4ee12453-aa54-4b27-a223-95dbfd3a1074' => 573, // South Australia
			'b62bb437-6636-470b-8317-8ac8a8436428' => 574, //Tasmania
			'5a6dcdf9-dec9-40ee-9a04-419680ae31c4' => 575, // Western Australia
			'6f754fe8-ec16-4d71-b013-537ef7b4f095' => 576, //Northern Territory
		];
		$key = array_search($code, $regions);
		if ($key === FALSE) {
			return '';
		}

		return $key;
	}
}
