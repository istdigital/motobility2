# Changelog

## 1.8.0 - 2020-04-29

- `MAJOR`: Added support for SEPA Credit Transfers
- `MAJOR`: Upgraded to Stripe PHP library v7 and to Stripe API version 2020-03-02
- Added support for subscription Coupons via Stripe Billing
- Implemented a new, more organized module configuration section in the admin area
- Customers can now change their subscriptions cards from their customer account section
- Subscriptions can now be disabled system-wide, improving performance
- The Apple Pay seller name can now be specified in the module configuration section
- The billing address of saved cards is now updated before placing the order, fixes a zip code verification failure
- Orders placed from the admin area can now also be marked as fraudulent by Stripe Radar
- Klarna integration updated to include shipping tax
- Webhook pings will now cleanup previously created products in Stripe. Added a CLI command to clean products created from older versions of the module.
- Updated locale translation files
- Added a missing ACH icon
- The refund amount that is displayed in the payment info block is now formatted based on the store currency and exchange rates
- The "View in Stripe" links in the payment info block will now recognize if the order was placed in Test Mode and link to the correct Stripe page
- Fixed an admin area initialization issue that was breaking the payment form in certain scenarios
- Fixed ACH refunds issue
- Fixed an issue with Apple Pay when terms and conditions must be manually checked
- Fixed an automatic invoicing issue when capturing a payment from the Stripe dashboard
- Fixed a configurable subscriptions refund issue
- Integration fix with latest FireCheckout
- If saved cards are disabled, hide the checkbox from the admin area's New Order page
- Payment intents are now stored in the customer session instead of the cache

## 1.7.1 - 2020-03-23

- Added an icon for ACH at the checkout
- ACH orders which are pending payment will now automatically create a pending invoice in the Magento admin
- ACH payments which are refunded from the Stripe dashboard will now automatically create a Credit Memo in Magento
- Various improvements with webhooks handling for multi-Stripe account configurations
- Updated FPX implementation based on changes to the webhooks API
- Backwards compatibility fixes in My Saved Cards section and in the Magento admin area for Magento 2.0 and 2.1
- Fixed a refund issue for multi-currency stores when the "Pay in store currency" setting is disabled
- Fixed a redirect issue with ACH when another APM was used before it in the same customer session
- Fixed a Stripe Elements initialization issue in the admin area

## 1.7.0 - 2020-03-13

- `MAJOR`: Added support for ACH bank transfers at the checkout.
- The "Pay in Store Currency" configuration option is no longer used for alternative payment methods, it is only used for card payments and wallets.
- Improved automatic webhooks configuration.
- When the Stripe PHP library dependency is missing, errors are now handled gracefully system-wide, all modules are automatically disabled, and an admin notification is displayed.
- Fixed a saved cards issue at the checkout.
- Fixed an integration problem with the BoostMyShop POS system.
- Improved REST API support - The Magento customer ID is now associated with the Stripe Customer ID in the database.
- Performance optimizations in the Magento admin area.

## 1.6.0 - 2020-02-21

- `MAJOR`: Added support for Klarna. Customers can now pay later or pay in installments.
- Automatic webhooks configuration will now also reconfigure existing webhook signing secrets.
- Automatic webhook configuration errors are now displayed in the Magento admin.
- Subscriptions initial fee is now a taxable amount.
- Terms and conditions are now displayed and validated below the Apple Pay button at the checkout page, when it is configured to be displayed above all payment methods.
- A webhooks queuing system has been added for events arriving at the same time.
- When a payment error or a 3DS authentication occurs, Magento order IDs no longer jump increment IDs for the 2nd payment attempt.
- Additional subscription info in the cart are now enabled by default.
- Moved Apple Pay configuration inside the Apple Pay section.
- Fixed Apple Pay amount not updating after a coupon is applied.
- Fixed some checkout javascript errors with alternative payment methods.
- Other minor code improvements.

## 1.5.2 - 2020-02-05

- Webhooks can now be automatically configured from the module's configuration section
- Bugfixes affecting older versions of v1.5.x
- Fixed Magento compilation issues with older versions of PHP

## 1.5.1 - 2019-12-10

- Fixes with Apple Pay affecting v1.5.0

## 1.5.0 - 2019-12-05

- `MAJOR`: Customers can now purchase multiple subscriptions and multiple regular products in the same shopping cart. Mixed carts also work in multi-shipping checkout and from the admin area.
- Added support for SetupIntents, which can be used to authorize the customer with trialing subscriptions, before the initial payment is collected.
- Card icons have been added to the checkout alongside the payment method title.
- Icons have been added to all alternative payment methods (European, China, Malaysia).
- The shipping cost for subscriptions can now be added as a separate recurring invoice item. In mixed subscription carts, shipping is recalculated on a per-subscription basis instead of a per-order calculation.
- Improved recurring order invoices, the tax and shipping will be displayed separately from the invoice grand total.
- Improved support for various OneStepCheckout modules, adjustments for better display of payment form in 3-column layouts.
- Payments which have only been authorized can now also be captured through cron jobs, not just from the admin area.
- Fixed a bug where changes in the billing address would not be passed to the Stripe API.
- India exports has been depreciated, performance optimizations after depreciation.

## 1.4.0 - 2019-11-01

- `MAJOR`: Recurring subscription payments will now generate new orders in Magento, instead of invoicing the old order multiple times. This allows for a better workflow with product shipments and inventory management, and fixes refund problems of order invoices.
- Added support for partial captures in Stripe; a partial invoice will now be correctly created in Magento through webhooks
- Both initial and recurring subscription orders will now display the full payment details in the Magento admin order page.
- Better handling of insufficient_funds card declined messages when buying subscriptions.
- Various fixes with webhooks when capturing or refunding payments from the Stripe dashboard - credit memos and invoices are now correctly created in Magento.
- Configurable products can no longer have any subscriptions configuration, fixes problems caused by user misconfiguration.
- Fixed a problem when capturing payments that had expired - in some cases the payment could not be recreated even if the customer had a saved card.
- Fixed a crash in the Magento admin area when viewing orders for products that have been deleted.
- Fixed a webhooks signature notice from the Magento log files.

## 1.3.1 - 2019-10-10

- Fixed quote loading issue when placing orders through the Magento REST API

## 1.3.0 - 2019-10-03

- Added SCA MOTO Exemptions support in the Magento admin
- Guest customers are now associated with their Stripe customer ID if they register immediately after placing an order
- The Stripe.js locale is now overwritten based on the Magento store view locale configuration
- Depreciated Email Receipt configuration option, this should now be disabled from the Stripe dashboard
- Added a partner ID in the module's app info
- Fixed placing subscription orders from the admin area
- Fixed refunds through the Stripe dashboard (no credit memo was being created)
- Fixed an installation problem with the Magento area code
- Fixed a Stripe account retrieval problem with some specific web server configurations

## 1.2.1 - 2019-09-18

- Compatibility fix with older versions of Magento 2
- Fixed card country not appearing in the Magento admin
- In some cases the Configure button in the admin area could not be clicked
- Improvements with subscription order invoicing
- Fix for configurable products when added to the card through the catalog or search pages

## 1.2.0 - 2019-08-27

- Added support for Stripe Billing / Subscriptions.
- Added support for the FPX payment method (Malaysia).
- Added support for 3D Secure v2 at the Multi-Shipping checkout page (SCA compliance)
- Added support for India exports as per country regulations. Full customer details are collected for all export sales.
- Added support for creating admin MOTO orders for guest customers (with no Magento customer login).
- Performance improvements (less API calls)
- Upgraded to Stripe API version 2019-02-19.
- The creation of Payment Intents is now deferred until the very final step of the checkout. Incomplete payment intents will no longer be shown in the Stripe Dashboard.
- The "Authentication Required" message at the checkout prior to the 3D Secure modal is now hidden completely
- Fixed an issue with capturing Authorized Only payments from the Magento admin area.
- Various fixes and improvements with Apple Pay

## 1.1.2 - 2019-06-10

- Improvements with multi-shipping checkout.
- Compatibility improvements with M2EPro and some other 3rd party modules.
- New translation entries.
- Fixed the street and CVC checks not displaying correctly in the admin order page.

## 1.1.1 - 2019-05-30

- Depreciates support for saved cards created through the Sources API.
- Improves checkout performance.
- Fixed error when trying to capture an expired authorization in the admin area using a saved card.
- Fixed a checkout crash with guest customers about the Payment Intent missing a payment method.

## 1.1.0 - 2019-05-28

- `MAJOR`: Switched from automatic Payment Intents confirmation at the front-end to manual Payment Intents confirmation on the server side. Resolves reported issue with charges not being associated with a Magento order.
- `MAJOR`: Replaced the Sources API with the new Payment Methods API. Depreciated all fallback scenarios to the Charges API.
- Stripe.js v2 has been depreciated, Stripe Elements is now used everywhere.
- When Apple Pay is used on the checkout page, the order is now submitted automatically as soon as the paysheet closes.
- Fixed: In the admin configuration, when the card saving option was set to "Always save cards", it wouldn't have the correct effect.
- Fixed: In the admin configuration, when disabling Apple Pay on the product page or the cart, it wouldn't have the correct effect.
- Fixed a multishipping page validation error with older versions of Magento 2.

## 1.0.0 - 2019-05-14

Initial release.
