<?php

use \Magento\Framework\App\Bootstrap;
use \Magento\Framework\DataObject;
use \Magento\Framework\App\Area;

include('app/bootstrap.php');

$Templateid = 5;
$orderid = 1;
$shipmentid = 1;

$bootstrap = Bootstrap::create(BP, $_SERVER);

$objectManager = $bootstrap->getObjectManager();

$state = $objectManager->get('Magento\Framework\App\State');

$state->setAreaCode('frontend');

try {
	$order = $objectManager->create('Magento\Sales\Model\Order')->load($orderid);

    // $transport = [
    //     'order' => $order,
    //     'billing' => $order->getBillingAddress(),
    //     'payment_html' => $objectManager->create('Magento\Payment\Helper\Data')->getInfoBlockHtml(
    //         $order->getPayment(),
    //         1
    //     ),
    //     'store' => $order->getStore(),
    //     'formattedShippingAddress' => $objectManager->create('Magento\Sales\Model\Order\Address\Renderer')->format($order->getShippingAddress(), 'html'),
    //     'formattedBillingAddress' => $objectManager->create('Magento\Sales\Model\Order\Address\Renderer')->format($order->getBillingAddress(), 'html'),
    // ];

    $shipment = $objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shipmentid);
    $transport = [
        'order' => $order,
        'shipment' => $shipment,
        'comment' => $shipment->getCustomerNoteNotify() ? $shipment->getCustomerNote() : '',
        'billing' => $order->getBillingAddress(),
        'payment_html' => $objectManager->create('Magento\Payment\Helper\Data')->getInfoBlockHtml(
            $order->getPayment(),
            1
        ),
        'store' => $order->getStore(),
        'formattedShippingAddress' => $objectManager->create('Magento\Sales\Model\Order\Address\Renderer')->format($order->getShippingAddress(), 'html'),
        'formattedBillingAddress' => $objectManager->create('Magento\Sales\Model\Order\Address\Renderer')->format($order->getBillingAddress(), 'html'),
    ];

    $transportObject = new DataObject($transport);

    $transport = $objectManager->create('Magento\Framework\Mail\Template\TransportBuilder')
	    ->setTemplateIdentifier($Templateid)
	    ->setTemplateOptions(
	        [
	            'area' => Area::AREA_FRONTEND,
	            'store' => 1
	        ]
	    )
	    ->setTemplateVars($transport)
	    ->setFrom("general")
	    //->addTo("rajalwaysfirst@gmail.com")
	    //->addTo("rajalwaysfirst@yahoo.com")
	    ->addTo("sam@i-st.com.au")
	    ->setReplyTo("test@gmail.com")
	    ->getTransport();

	echo $transport->getMessage()->getRawMessage();
	$transport->sendMessage();

} catch (\Exception $e) {
    echo $e->getMessage();       
}