<?php

$filename = __DIR__ . "/stripe-php/init.php";

// The Stripe class should exist if the Stripe PHP library has been installed with composer.
if (!class_exists('\Stripe\Stripe') && file_exists($filename))
    require_once $filename;

// Throws an error if the Stripe PHP library was installed neither with composer, nor manually by placing it in this directory
if (!class_exists('\Stripe\Stripe'))
    throw new \Exception("The Stripe PHP library is not installed. Please see the Stripe module installation instructions for details.");

\Stripe\Stripe::setApiVersion("2019-02-19");
\Stripe\Stripe::setAppInfo("Stripe Payments M2", "2.6.0", "https://stripe.com/docs/magento/cryozonic");
