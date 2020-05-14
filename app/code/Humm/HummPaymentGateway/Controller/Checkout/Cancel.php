<?php

namespace Humm\HummPaymentGateway\Controller\Checkout;

/**
 * @package Humm\HummPaymentGateway\Controller\Checkout
 */
class Cancel extends AbstractAction {

    public function execute() {
        $orderId = $this->getRequest()->get( 'orderId' );
        $order   = $this->getOrderById( $orderId );

        if ( $order && $order->getId() ) {
            $this->getLogger()->debug( 'Requested order cancellation by customer. OrderId: ' . $order->getIncrementId() );
            $this->getCheckoutHelper()->cancelCurrentOrder( "Humm: " . ( $order->getId() ) . " was cancelled by the customer." );
            $this->getCheckoutHelper()->restoreQuote(); //restore cart
            $this->getMessageManager()->addWarningMessage( __( "You have successfully canceled your humm payment. Please click on 'Update Shopping Cart'." ) );
        }
        $this->_redirect( 'checkout/cart' );
    }

}
