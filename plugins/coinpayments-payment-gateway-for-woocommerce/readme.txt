=== CoinPayments.net Payment Gateway for WooCommerce ===
Contributors: CoinPayments, woothemes, mikejolley, jameskoster, CoenJacobs
Donate link: https://gocps.net/3ncyzcq3sy0ww1rxghleip1aky/
Tags: bitcoin, litecoin, altcoins, altcoin, dogecoin, feathercoin, netcoin, peercoin, blackcoin, darkcoin, ripple, ethereum, ether, woocommerce
Requires at least: 3.7.0
Tested up to: 5.8.0
Stable tag: 1.0.14
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin implements a payment gateway for WooCommerce to let buyers pay with Bitcoin, Litecoin, Ripple, and other cryptocurrencies via CoinPayments.net.

== Description ==

This plugin implements a payment gateway for WooCommerce to let buyers pay with Bitcoin, Litecoin, Ripple, and other cryptocurrencies via CoinPayments.net.

== Installation ==

1. Upload the `coinpayments-payment-gateway-for-woocommerce` directory to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. In the WooCommerce Settings page go to the Payment Gateways tab, then click CoinPayments.net.
1. Check "Enable CoinPayments.net" and enter your CoinPayments.net Merchant ID and IPN Secret (a long random string you define for security).
1. Click "Save changes" and the gateway will be active.

== Changelog ==

= 1.0.14 =
* Updated WordPress compatibility tag.

= 1.0.13 =
* Logo update.

= 1.0.12 =
* Improved compatibility with new WooCommerce versions.
* Updated WordPress compatibility tag.

= 1.0.11 =
* Minor URL escaping fix.

= 1.0.10 =
* Added support for Aelia Currency Switcher.
* Removed support for deprecated HTTP Auth IPN verification mode.

= 1.0.9 =
* Added compatibility mode to help work with various WooCommerce addons.

= 1.0.8 =
* Changed zero-confirm to 1st-confirm for better reliability.

= 1.0.7 =
* Updated to support WooCommerce 2.3.0

= 1.0.6 =
* Adds support for 0-confirm payments (for digital downloads.)
* Changes plugin so it will continue to log payment status updates even after marked as paid.

= 1.0.5 =
* Adds option to not send shipping information to the CoinPayments.net checkout page.
* Possible workaround for WooCommerce order ID bug.

= 1.0.4 =
* Modified to count "Queued for nightly payout" as order completion

= 1.0.3 =
* Fix to work with WooCommerce 2.1.0

= 1.0.2 =
* Added additional order completion check

= 1.0.1 =
* Fixed image URL for new folder name.

= 1.0.0 =
* Initial release.
