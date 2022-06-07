=== Ultimate Auction for WooCommerce ===
Contributors: nitesh_singh
Tags: woocommerce auction, woocommerce auction plugin, woocommerce auction theme, woocommerce bidding, wordpress auction, wordpress auction plugin, wordpress bidding plugin
Requires at least: 5.5
Tested up to: 5.9.3
Stable tag: 2.2.4
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Awesome plugin to host auctions on your WooCommerce powered site and sell your products as auctions.

== Description ==

[Main Site](https://auctionplugin.net/) | [PRO Features](https://auctionplugin.net/features) | [Docs](https://docs.auctionplugin.net)

Ultimate Auction for WooCommerce allows easy and quick way to add your products as auctions on your site.
Simple and flexible, Lots of features, very configurable.  Easy to setup.  Great support.

*   [PRO Version - Upgrade to unlock awesome features &raquo;](https://auctionplugin.net?utm_source=wordpress.org&utm_medium=link&utm_campaign=woo-auction-from-wp.org)

 
 = PRO Features =
 
	1. Collect Credit Card and Automatically Debit Winning Amount
	2. Users can add auctions
	3. Automatic or Proxy Bidding
	4. SMS Notification 
	5. Soft-Close or Anti-Sniping feature to extend time
	6. Automatic and Manual Relisting of Expired Auction
	7. Add Auction for Future Dates.
	8. Add Silent auctions
	9. Variable Increment
	10. Buyer's Premium
	11. Reverse Bidding Engine
	12. Bulk Import
	13. Live Bidding without page refresh
	14. Delete User Bids
	15. Support Virtual Products
	16. WPML and LocoTranslate Compatible
	17. Widgets - Expired, Future 
	18. Custom Emails
	19. Many Shortcodes & Filters 
	
= Free Features =

    1. Registered User can place bids 
	2. Ajax Admin panel for better management.
    3. Add standard auctions for bidding
    4. Buy Now option    
    5. Show auctions in your timezone        
    6. Set Reserve price for your product
	7. Set Bid incremental value for auctions
	8. Ability to edit, delete & end live auctions
	9. Re-activate Expired auctions
	10. Email notifications to bidders for placing bids
    11. Email notification to Admin for all activity
    12. Email Sent for Payment Alerts
	13. Outbid Email sent to all bidders who has been outbid.
	14. Count Down Timer for auctions.	
	15. Ability to Cancel last bid 
    and Much more...

== Installation ==
= Minimum Requirements =

* PHP version 5.2.4 or greater (PHP 7.2 or greater is recommended)
* MySQL version 5.0 or greater (MySQL 5.6 or greater is recommended)
* [Latest WooCommerce Plugin](https://wordpress.org/plugins/woocommerce)

= Automatic installation =

1. To do an automatic install of our plugin, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.


2. In the search field type “WooCommerce Auction” and click Search Plugins. Once you’ve found "Ultimate WooCommerce Auction Plugin" by "Nitesh Singh", you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking “Install Now”. 


3. Once installed, simply click "Activate".


4.  After you have setup WooCommerce and activated our plugin, you should add a product. You can do it via Wordpress Dashboard, navigate to the Products menu and click Add New.


5. 	While adding product, choose "product data = Auction Product". Add data to relevant fields and publish it. 

Your auction product should now be ready and displayed under "Shop" page. If you have problems please open discussion in support forum.

= Manual installation =

The manual installation method involves downloading our plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation). Kindly follow these instructions for [WooCommerce Plugin](https://wordpress.org/plugins/woocommerce) and then for our plugin.

Then kindly follow step 4 and 5 from "Automatic Installation". 

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

If on the off-chance you do encounter issues with the shop/category pages after an update you simply need to flush the permalinks by going to WordPress > Settings > Permalinks and hitting 'save'. That should return things to normal.

== Frequently Asked Questions ==

= Where can I get support or talk to other users? =

If you get stuck, you can ask for help in the [Ultimate WooCommerce Auction Plugin Forum](https://wordpress.org/support/plugin/ultimate-woocommerce-auction/).

= Will this plugin work with my theme? =

Yes; It will work with any WooCommerce supported theme, but may require some styling to make it match nicely. 

= Where can I request new features, eCommerce themes and extensions? =

You can write to us: nitesh@auctionplugin.net. 

= What shortcodes are available for this plugin = 
1. Shortcode to display new auctions.

	[uwa_new_auctions days_when_added="10" columns="4" orderby="date" order="desc/asc" show_expired="yes/no"]

Field details:

days_when_added = Only display 10 days auctions on creation date..
columns = Default woocommerce
orderby = Default woocommerce
order = Default woocommerce
show_expired = Default is yes. if we select "no"  then expired auction will not be displayed.

= What Hooks are available for this plugin = 
1) If you are going to add some custom text before "Bidding Form",this hook should help you. 

	ultimate_woocommerce_auction_before_bid_form
 
Example of usage this hook
 
	add_action( 'ultimate_woocommerce_auction_before_bid_form', 'here_your_function_name');
	function here_your_function_name() 
	{   
		echo 'Some custom text here';   
	}

2) If you are going to add some custom text after "Bidding Form",this hook should help you. 

	ultimate_woocommerce_auction_after_bid_form

Example of usage this hook
 
	add_action( 'ultimate_woocommerce_auction_after_bid_form', 'here_your_function_name');
		function here_your_function_name() {   
			echo 'Some custom text here';   
		}

3) If you are going to add some custom text before "Bidding Button",this hook should help you. 

	ultimate_woocommerce_auction_before_bid_button
 
Example of usage this hook
 
	add_action( 'ultimate_woocommerce_auction_before_bid_button', 'here_your_function_name');
		function here_your_function_name() {   
			echo 'Some custom text here';   
		}

4) If you are going to add some custom text after "Bidding Button",this hook should help you. 

	ultimate_woocommerce_auction_after_bid_button
Example of usage this hook
 
		add_action( 'ultimate_woocommerce_auction_after_bid_button', 'here_your_function_name');
			function here_your_function_name() {   
				echo 'Some custom text here';   
			}

5)You can use this hook while auction is closing

	ultimate_woocommerce_auction_close


		add_action( 'ultimate_woocommerce_auction_close', 'here_your_function_name', 50 );
			function here_your_function_name($auction_id) {
   
				$product = wc_get_product($auction_id);
  
				//Your Custom Code here
   
			}

6) You can use this hook while admin deletes bid.

	ultimate_woocommerce_auction_delete_bid



		add_action( 'ultimate_woocommerce_auction_delete_bid', 'here_your_function_name', 50 );
			function here_your_function_name($auction_id) {
   
				$product = wc_get_product($auction_id);
  
				//Your Custom Code here
   
			}

= What Filters are available for this plugin = 

1) Product Condition

	ultimate_woocommerce_auction_product_condition

How to use this filter ?

Copy paste in your functions.php file of theme as per your requirement.

	add_filter('ultimate_woocommerce_auction_product_condition', 'here_your_function_name' );
		function here_your_function_name( $array ){
			/*
			 Exiting array keys. 1)new 2)used
			*/
			$array['new']='New2';
			$array['used']='Used2';
			
			/*
			 You can Add New Condition to Auction Product Like below.
			*/
			$arr2 = array('new_factory' => 'Factory Sealed Packaging');
			$arr3 = array('vintage' => 'Vintage');
			$arr4 = array('appears_new' => 'Appears New');
			$array = $array + $arr2 + $arr3 + $arr4;

			return $array;
		} 


2) Bid Button Text

	ultimate_woocommerce_auction_bid_button_text

How to use this filter ?
Answer : Copy paste in your functions.php file of theme as per your requirement.

	add_filter('ultimate_woocommerce_auction_bid_button_text', 'here_your_function_name' );
	function here_your_function_name(){

		 return __('Button Text', 'ultimate-woocommerce-auction');
	} 
-----------------------------------------------------------------
3) Heading for Total Bids

	ultimate_woocommerce_auction_total_bids_heading

How to use this filter ?

Answer: Copy paste in your functions.php file of theme as per your requirement.
	
	add_filter('ultimate_woocommerce_auction_total_bids_heading', 'here_your_function_name1' );
	function here_your_function_name1(){

		 return __('Total Bids Placed:', 'ultimate-woocommerce-auction');
	} 
-----------------------------------------------------------------
4) Pay Now Button 

	ultimate_woocommerce_auction_pay_now_button_text

How to use this filter ?

Answer : Copy paste in your functions.php file of theme as per your requirement.

	add_filter('ultimate_woocommerce_auction_pay_now_button_text', 'here_your_function_name' );
	function here_your_function_name(){

		return __('Pay Now Text', 'ultimate-woocommerce-auction');
	} 
-----------------------------------------------------------------

== Screenshots ==

1. Admin: Create auction product
2. Admin: Create auction product with data
3. Admin: Main Plugin Settings
4. Admin: Live Listing
5. Admin: Expired Listing
6. Frontend: Shop Page
7. Frontend: Single product page example


== Changelog ==

= 2.2.4 =

1. Improvement:
	
	When an auction product is trashed then we have now made sure that under my-account -> my auctions and my auctions-watchlist menu that specific auction product is not visible.

= 2.2.3 =

1. Fix:
	
	Error was displayed when polylang plugin is activated. This issue has now been fixed.

2. Improvement:

	Any user can view bidder name in bids list of auction detail page. Previously the bidder name was masked in asterisk
 

= 2.2.2 =

1. Fix:

	Error was displayed on My-account -> My-auctions page with PHP8. This issue has now been fixed.

= 2.2.1 =

1. Fix:

	Buy Now will now expire an auction product - There was an issue when user clicks buy now and buys an auction product then the auction product was not expiring. This has been fixed.

	Few Strings were not translation ready. We have now fixed them so that they can be translated.
	
= 2.2.0 =

1. Fix:

	Few Strings were not translation ready. We have now fixed them so that they can be translated.
	
= 2.1.9 =
1. Fix:

	We have fixed deprecated meta issue. Replaced deprecated "WC_Order::get_item_meta" with latest function wc_get_order_item_meta.

	We have fixed Pro plugin banner dismiss issue. When this notice is dismissed from one page, it will be dismissed from all pages and it will not be displayed again even after refreshing the page.


= 2.1.8 =
1. Fix:

	In their latest update of WooCommerce plugin, CSS code of it was conflicting with our plugin's css and due to it the design of Cart page was not proper in mobile view. We have fixed this issue. 


= 2.1.7 =
1. Improvement
	
	When any auction product is added in the cart using "Buy Now" and if it is "deleted from cart" then the product will return back to its original auction state. We have made sure that this happens now.

2. Fix:

	Adding and removing Watchlist functions have been updated to use Nonce functions such as wp_create_nonce
	
	Place Bid button when clicked without any bid was throwing critical error in PHP 8. This has been fixed now.
	
	HTML has been properly escaped including the echo'd code.
	
	
= 2.1.6 =
1. Fix:
	
	We had observed that plugin was not installing properly on PHP 8. This has been resolved now.

	
= 2.1.5 =
1. Fix:

	Delete Bid option was not working. This has now been fixed.
	
	
= 2.1.4 =

1. Fix:

	We have fixed the issue where auction product was going in "Pending" state if it was saved as draft or added as a duplicate.
	
	
= 2.1.3 = 

1. Fix: 
	
	We have sanitized, validated, and escaped all functions using POST/GET/REQUEST/FILE calls for meeting security guidelines of Wordpress.org

	We have also renamed our plugin from "Ultimate WooCommerce Auction Plugin" to "Ultimate Auction for WooCommerce" to avoid any trademark infringement.
	
	Text Domain of the plugin has been changed from "woo_ua" to "ultimate-woocommerce-auction". This change was made to match the permalink of the plugin as this was required as per the guidelines from Wordpress.org.

= 2.1.2 = 

1. Fix: Winner email’s “Pay Now” button was earlier redirecting to WordPress default login Page. It has been rectified and is now redirecting to “My Account Page”.


= 2.1.1 =

1. Fix: The count inside My Account > Auctions > Watchlist was incorrect. We have fixed and tested this. 

2. Fix: SKU given for "Auction Product Type" is now searchable. We have fixed this and Product search by SKU will show auction products.

3. Fix: We had received an issue of multiple email though we were not able to reproduce this problem in-house. We have introduced a flag in the code which will ensure that email function is called only one time and if any customer has experienced multiple email then this fix should solve it too.

= 2.1.0 = 

1. New Feature: We have added a new setting where admin can choose to enable a "Bidding Confirmation Modal". When enabled then a confirmation pop-up will be displayed to the bidder when he will attempt to bid.


= 2.0.10 =

1. New Feature: We have added a new configuration for admin to restrict admin's bidding on the auction product.

2. Fix: Plugin now supports latest Time and Date functions introduced in Wordpress 5.3.2 version.

3. Fix: Converting simple product to auction product and vice versa was causing issue with the product. This has now been resolved.

4. Fix: There was an issue with duplicating an auction product. This has now been resolved.

5. Fix: Wordpress Notice of Undefined Error has been resolved.
	Notice: Undefined index: action in E:\xampp\htdocs\UWAFREE\wp-content\plugins\ultimate-woocommerce-auction\includes\admin\class-uwa-admin.php on line 203 (done)
	
6. Improvement: We have removed "bid value" text and included "currency" symbol for a more cleaner look.

7. Improvement: We have added pagination to the shortcode. Here is full shortcode with parameters [uwa_new_auctions days_when_added="100" columns="4" orderby="date" order="desc/asc" show_expired="yes/no" paginate="true/false" limit="4"]

8. Improvement: Wrong English Grammer has been rectified for few texts.


= 2.0.9 = 

1. New Feature - Plugin is now compatible with WPML and also with "My WP Translate" plugin.

2. New Feature - Redirection to Auction Detail page - When any visitor (without logged in) used to visit auction product detail page, they were prompted to login/Register which upon click opens "My Account" page. And then after login or registration, it did not redirect back to Auction Product Detail page. We have now included that feature. 

3. Fix - HTTP POST request was being called with each page load to check expiration status. This was redundant and not required and thus have been removed.

= 2.0.8 = 

1. Fix - Removed unwanted echo left inside plugin.

= 2.0.7 = 

1. New Feature - Timer on auction product detail page can be shown H:M:S format too. New setting is added under Settings > Auction Detail Page > Enable the Checkbox for hiding compact countdown format.

2. Fix - Few emails have incorrect English sentences. This has been rectified.

3. Fix - Error was happening when both PRO and FREE were active together. This has been fixed.


= 2.0.6 = 

1. New Feature - Plugin is now multi-site compatible

2. Fix - Warnings were appearing when first bid was placed in an auction. This is now fixed.

3. Fix - Few texts were having translation issue. We have added them.

4. Fix - Plugin will now appear inside WP Admin Dashboard on LHS as "Auctions" instead of "Ultimate Woo Auction"


= 2.0.5 = 

1. New Feature - Added two new HTML CLASSES which developers can use for CSS. 
Expired Auction class = uwa_auction_status_expired
Live Auction class = uwa_auction_status_live

2. New Feature - WooCommerce compatibility versions have been added inside our plugin description to show it inside Admin Panel -> Installed plugins

3. Fix - For expired auctions, if we were browsing to Admin -> WC -> Products page and editing the end date to future date for a specific product then it wont change state of auction to Live. We have now fixed this problem by disabling the end date for editing.


= 2.0.4 = 

1. Fix - Plugin will display message to login/register when any non-logged in visitor tries to place bid. In few themes we received queries that "Login/register" message was not being displayed when non-logged in visitor tries to place bid. This problem is now fixed.

2. Fix - Description tab will be displayed in 1st position of auction detail page.

3. Fix - Shop Manger Role was not able to see user's full name. This issue has been fixed.

4. Fix - GOTMLS.NET was detecting malware in 2 files due to code commenting style (/* ...*/). We have changed the commenting style just for the sake of no detection as we think its a false positive.

5. New Feature - Added a new filter "ultimate_woocommerce_auction_pay_now_button_text". Details of its usage are mentioned in FAQs.


= 2.0.3 = 

1. New Feature - Added hooks & filters. Full documentation available in README FAQ section.


= 2.0.2 = 

1. New Feature - Plugin now allows to add your "buy now" and "won by bids" item to checkout page. So, if a user has added simple products to his cart and also won products via auction then all his products will be added to his checkout page.

2. New Feature - New Shortcode to display "Latest Auction". Shortcode format is [uwa_new_auctions days_when_added="10" columns="4" orderby="date" order="desc/asc" show_expired="yes/no"].

3. New Feature - User can hide their names from bidding page. User can go to their My Account Page -> Auction Setting to access this setting.

4. Fix - Bid Value field now accepts amount in same currency format as defined in WooCommerce.

= 2.0.1 =

1. Fix - Decimal pricing is now supported for auction products. Also, WooCommerce normal products will have decimal pricing.


= 2.0.0 = 

1. New Feature - Plugin has a new layout and is accessible from WP Admin -> LHS bar -> Ultimate Woo Auction. It has Settings page and auctions list which shows live and expired auctions.

2. Fix - My Auction / My Auctions Watchlist are now added to plugin's text domain i.e. woo_ua. Previously they were in "woocommerce" text domain.

= 1.0.6 = 

1. Fix - Customer noticed an issue that when we translate using WPML then Viewing Auction Watchlist slug does not change. This has been fixed.

= 1.0.5 = 

1. Fix - End Date Issue where date picker was not working with latest WP has been fixed.


= 1.0.4 =

1. New Feature - Layout for Settings page has been changed for better readability and more configurations have been added.

2. Fix - Configuration for Ajax update of bid information was not working previously. This has now been fixed.

= 1.0.3 =

1. New Feature - New column "Auction Status" added under Products -> All Products. This shows whether auction is "Live" or "Expired".

2. Fix - Edition in bidding logic where in user can now increase their bid. Previously if any user had highest bid then he was not able to increase his bid. This modification will help user to reach reserve price. 

3. Fix - "Add to Watchlist" was not working. This has now been fixed.


= 1.0.2 =

* Fix: Text Domain added  which will enable to work with LocoTranslate.

= 1.0.1 =

* Fix: Bid field's width has been increased to handle 9 digits in auction detail page.
* Fix: Plugin's settings are now consolidated under single link.
* Fix: Minor design changes has been done in Auction Detail page for better representation of data.

= 1.0 =
Initial Release