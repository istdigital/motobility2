<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Plugin\Quote\Model;

use \Magento\Checkout\Model\Session as CheckoutSession;

class Totals
{
    protected $helper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    public function __construct(
        \Amasty\Preorder\Helper\Data $helper,
        CheckoutSession $checkoutSession
    ) {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
    }

    public function afterGetItems(\Magento\Quote\Model\Cart\Totals $subject, $quoteItems)
    {
        if ($this->helper->preordersEnabled()) {
            /** @var \Magento\Quote\Model\Quote  */
            $quote = $this->checkoutSession->getQuote();

            foreach ($quoteItems as &$item) {
                $quiteItem =  $quote->getItemById($item->getItemId());
                $note = $this->helper->getQuoteItemPreorderNote($quiteItem);
                if ($note) {
                    $item->setData('preorder_note', $note);
                }
            }
        }

        return $quoteItems;
    }
}
