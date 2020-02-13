<?php

namespace Cryozonic\StripePayments\Api;

interface ServiceInterface
{
    /**
     * Returns Redirect Url
     *
     * @api
     * @return string Redirect Url
     */
    public function redirect_url();

    /**
     * Gets the created payment intent at the checkout
     *
     * @api
     *
     * @return mixed Json object containing the new PI ID.
     */
    public function get_payment_intent();
}
