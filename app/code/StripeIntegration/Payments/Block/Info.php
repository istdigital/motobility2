<?php

namespace StripeIntegration\Payments\Block;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;
use StripeIntegration\Payments\Gateway\Response\FraudHandler;

class Info extends ConfigurableInfo
{
    /**
     * Returns label
     *
     * @param string $field
     * @return Phrase
     */
    protected function getLabel($field)
    {
        return __($field);
    }

    /**
     * Returns value view
     *
     * @param string $field
     * @param string $value
     * @return string | Phrase
     */
    protected function getValueView($field, $value)
    {
        switch ($field) {
            case FraudHandler::FRAUD_MSG_LIST:
                return implode('; ', $value);
        }
        return parent::getValueView($field, $value);
    }

    /**
     * @var array
     */
    protected $transactionFields = [
        'bic'=> 'BIC',
        'iban_last4' => 'IBAN Last4'
    ];

    /**
     * Get some specific information in format of array($label => $value)
     *
     * @return array
     */
    public function getSpecificInformation()
    {
        // Get Payment Info
        /** @var \Magento\Payment\Model\Info $_info */
        $_info = $this->getInfo();

        $source_info = $_info->getAdditionalInformation('source_info');
        $source_info = json_decode($source_info, true);

        if ($source_info) {
            $result = [];
            foreach ($source_info as $field => $value)
            {
                if (isset($this->transactionFields[$field]))
                    $description = $this->transactionFields[$field];
                else
                    $description = ucwords(implode(" ", explode('_', $field)));

                $result[$description] = $value;
            }

            return $result;
        }

        return $this->_prepareSpecificInformation()->getData();
    }

    public function getCompany()
    {
        // Get Stripe Account info
        try
        {
            $storeId = $this->_helper->getStoreId();
            $businessName = $this->_scopeConfig->getValue("payment/stripe_payments_sepa/business_name", ScopeInterface::SCOPE_STORE, $storeId);

            if (empty($businessName))
            {
                $account = \Stripe\Account::retrieve();
                $businessName = $account->business_name;
            }

            if (empty($businessName))
                throw new \Exception("No business name set");
        }
        catch (\Exception $e)
        {
            $businessName = "Our Business";
        }
    }

    public function cardType($code)
    {
        return $this->helper->cardType($code);
    }
}
