<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Zemez\Newsletter\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;

class Subscriber extends \Magento\Newsletter\Model\Subscriber
{
    /**
     * Sends out confirmation success email
     *
     * @return $this
     */
    public function sendConfirmationSuccessEmail()
    {
        parent::sendConfirmationSuccessEmail();
        $email = $this->getEmail();
        $mail = ObjectManager::getInstance()->get(\Zemez\Newsletter\Model\Mail::class);
        $mail->send(
            $email,
            ['data' => new DataObject(['email' => $email])]
        );
        return $this;
    }
}
