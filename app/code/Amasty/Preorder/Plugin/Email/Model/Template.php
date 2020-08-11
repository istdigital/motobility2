<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Plugin\Email\Model;

use Amasty\Preorder\Helper\Data;
use Magento\Email\Model\Template as NativeTemplate;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;

class Template
{
    /**
     * @var OrderIdentity
     */
    private $orderIdentity;

    /**
     * @var null|Order
     */
    private $order = null;

    /**
     * @var Data
     */
    private $preorderHelper;

    public function __construct(OrderIdentity $orderIdentity, Data $preorderHelper)
    {
        $this->orderIdentity = $orderIdentity;
        $this->preorderHelper = $preorderHelper;
    }

    /**
     * @param NativeTemplate $subject
     * @param array $variables
     *
     * @return array
     */
    public function beforeGetProcessedTemplate($subject, $variables = [])
    {
        if (isset($variables['order']) && $variables['order'] instanceof \Magento\Sales\Model\Order) {
            $this->order = $variables['order'];
        }

        return [$variables];
    }

    /**
     * @param NativeTemplate $subject
     * @param string $result
     *
     * @return string
     */
    public function afterGetProcessedTemplate($subject, $result)
    {
        if ($this->preorderHelper->isWarningInEmail()) {
            $templateCode = null;
            
            if ($this->order && $this->preorderHelper->getOrderIsPreorderFlag($this->order)) {
                $templateCode = $this->order->getCustomerIsGuest()
                    ? $this->orderIdentity->getGuestTemplateId()
                    : $this->orderIdentity->getTemplateId();
            }

            if ($templateCode && $templateCode == $subject->getId()) {
                $warningBlock = sprintf(
                    '<p style="padding: 10px; background-color: #f5f5f5; font-weight: 400;">%s</p>',
                    $this->preorderHelper->getOrderPreorderWarning($this->order->getId())
                );
                $result = preg_replace(
                    '@<[^>]*class=["\'][^"\']*greeting[^>]*>@',
                    $warningBlock . '$0',
                    $result,
                    1
                );
            }
        }

        return $result;
    }
}
