<?php

namespace Zemez\Mail\Plugin;

use Magento\Store\Model\ScopeInterface;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder {
	protected function prepareMessage() {
		parent::prepareMessage();

		$scopeConfig = $this->objectManager->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
		$storeEmails = [];
		$storeEmails[] = $scopeConfig->getValue("trans_email/ident_general/email", ScopeInterface::SCOPE_STORE);
		$storeEmails[] = $scopeConfig->getValue("trans_email/ident_sales/email", ScopeInterface::SCOPE_STORE);
		$storeEmails[] = $scopeConfig->getValue("trans_email/ident_support/email", ScopeInterface::SCOPE_STORE);
		$storeEmails[] = $scopeConfig->getValue("trans_email/ident_custom1/email", ScopeInterface::SCOPE_STORE);
		$storeEmails[] = $scopeConfig->getValue("trans_email/ident_custom2/email", ScopeInterface::SCOPE_STORE);
		$storeEmails[] = $scopeConfig->getValue("contact/email/recipient_email", ScopeInterface::SCOPE_STORE);

		$emailObj = \Zend\Mail\Message::fromString($this->message->getRawMessage());

		$hasStoreEmail = false;

		foreach ($storeEmails as $e) {
			if ($emailObj->getTo()->has($e)) {
				if (isset($this->templateVars['order'])) {
					$order = $this->templateVars['order'];
					$this->message->setReplyTo($order->getCustomerEmail());
				} else if (isset($this->templateVars['customerEmail'])) {
					$email = $this->templateVars['customerEmail'];
					$this->message->setReplyTo($email);
				}
				$hasStoreEmail = true;
				break;
			}
		}

		if (!$hasStoreEmail) {
			$this->message->setReplyTo($storeEmails[0]);
		}

		$generalName = $scopeConfig->getValue("trans_email/ident_general/name", ScopeInterface::SCOPE_STORE);
		$noreply = 'noreply' . substr($storeEmails[0], strpos($storeEmails[0], "@"));

		$this->message->setFromAddress($noreply, $generalName);

		return $this;
	}

}
