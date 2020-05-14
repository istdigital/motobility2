# Humm Payment Gateway Module for Magento 2

The following instructions detail how to install the **humm** payment gateway on the Magento 2 platform.

This assumes that you have signed the required Merchant Agreement and have been provided a Merchant Id and API Key.

## Install using Composer 

 
1. Add the **humm** repository

        composer config repositories.shophumm git https://github.com/shophumm/humm-magento2.x.git

2. Require the Humm Payment Gateway Module

        composer require humm/module-humm-payment-gateway:dev-master

3. Enable the module
       
        ./bin/magento module:enable Humm_HummPaymentGateway --clear-static-content

4. Update the database

        ./bin/magento setup:upgrade

5.  Configure the plugin

Login to the  administration interface and go to:
  
 * Stores -> Configuration -> Sales -> Payment Methods 

 * Scroll Down to "Other Payment Methods" and select "Humm Payment Gateway" 

 * Enter your Merchant Number and API Key and select "Save Config" in the top right of the screen. 


## Getting help. 

If you would like assistance with the installation of the plugin or you need an API key, please contact the **humm** Platform Integration Team pit@shophumm.com.au

Please see https://docs.shophumm.com.au for information on how to use this plugin. 
