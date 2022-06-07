=== Delete Me ===
Contributors: cmc3215
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=L5VY6QDSAAZUL
Tags: delete, unsubscribe, user management, gdpr, multisite
Requires at least: 3.7
Tested up to: 5.7
Stable tag: 3.0
Requires PHP: 5.2.4
License: GPL2 http://www.gnu.org/licenses/gpl-2.0.html

Allow users with specific WordPress roles to delete themselves from the Your Profile page or anywhere Shortcodes can be used.

== Description ==

Allow users with specific WordPress roles to delete themselves from the **Your Profile** page or anywhere Shortcodes can be used using the Shortcode `[plugin_delete_me /]`.
Settings for this plugin are found on the **Settings -> Delete Me** subpanel. Multisite and Network Activation supported.

**How it works:**

* A user clicks the delete link, which defaults to "Delete Account", but can be changed.

* User is asked to confirm they want to delete themselves.

* If confirmed, user and all their Posts, Links, and (optionally) Comments are deleted.

* Deleted user (optionally) redirected to landing page URL, default is homepage, can be changed or left blank.

**Settings available:**

* Enable or disable Network Wide, which applies a single page of settings across all Multisite network Sites.

* Select specific WordPress roles (e.g. Subscriber) you want to allow to delete themselves using Delete Me.

* `class` and `style` attributes of the delete link.

* `<a>` tag clickable content of the delete link.

* Landing page URL.

* **Your Profile** confirmation page Heading, Warning, Password (optionally require password), Button.

* Enable or disable delete link on the **Your Profile** page.

* Javascript confirm text for Shortcode.

* Enable or disable Javascript confirm for Shortcode.

* Enable or disable using a form (require password) instead of a link for Shortcode.

* Multisite: Delete user from entire Network or current Site only.

* Delete comments.

* E-mail notification when a user deletes themselves.

== Installation ==

**Basic**

1. Install automatically in WordPress on the **Plugins -> Add New** subpanel or upload the **delete-me** folder to the **/wp-content/plugins/** directory.

2. Activate the plugin on the **Plugins** panel in WordPress.

3. Go to the **Settings -> Delete Me** subpanel, check the WordPress roles you want to allow to delete themselves using Delete Me, and click Save Changes.

4. Thats it! The delete link will be placed automatically near the bottom of the **Your Profile** page for roles you allowed. If you prefer the delete link be on your front-end, please read below.

**Advanced: Shortcode Usage**

*Note: To prevent the delete link from appearing on the **Your Profile** page, uncheck the setting **Link Enabled**.*

* **Custom Profile Page** - Copy and paste the Shortcode `[plugin_delete_me /]` into the Post or Page you've created.
* **Theme File** - To call the Shortcode in one of your .php Theme files, use `<?php echo do_shortcode( '[plugin_delete_me /]' ); ?>`.
* **Text Widget** - To use the Shortcode in a Text Widget, make sure the line `add_filter( 'widget_text', 'do_shortcode' );` appears in your Theme Functions (functions.php) file.
* **Attributes** - The attributes `class, style, html, js_confirm_warning, landing_url` may be used to override settings, but are not required. They provide site owners the ability to use multiple languages and/or multiple links within the same site or even the same page each configured differently.

**Advanced: Translation**

*Note: Languages directory for plugins: **/wp-content/languages/plugins***

1. Choose a translation method:
**A)** Get or provide [translations of this plugin on WordPress.org](https://translate.wordpress.org/projects/wp-plugins/delete-me). Alternatively, some language plugins (e.g. [Polylang](https://wordpress.org/plugins/polylang/)) can download and install the publicly available language files for plugins.
**B)** Create your own translations using the included **delete-me.pot** template file found in this plugin's languages directory and the free tool [Poedit](https://poedit.net).
1. Once you have finished your translations, export and save them as a .mo file.
1. Name the .mo file delete-me-{locale}.mo (e.g. delete-me-en_US.mo)
1. Place the .mo file into the languages directory for plugins.

*If you're using Poedit, it will create a .po file too. You should keep the .po file in order to open and save changes to your translations or to update the .po file with source text changes when an updated delete-me.pot file is released. Just be aware that the actual translations used by the plugin are inside the .mo file.*

***Warning: If you place your translation files into this plugin's languages directory instead of the directory shown above, they will be deleted or replaced with any the author might include when updating the plugin.***

== Frequently Asked Questions ==

= What happens to Posts, Links, and (optionally) Comments belonging to a deleted user? =

Most Post types and Comments are moved to Trash. Links are always deleted permanently.

= Does this plugin support WordPress Multisite? =

Yes, Network Activation and single Site activation are both supported. Users and their content will only be deleted from the Site they delete themselves from, other Network Sites will be unaffected.

= When using Multisite, are users deleted from the Network or only the Site deletion originated from? =

By default, users registered to multiple Sites on the Network are only deleted from the current Site and will remain registered to their remaining Sites. However, if the setting "Delete From Network" is checked, users will be deleted from the entire Network.

= Is it possible for a user to delete anyone but themselves? =

No, the user deleted is the currently logged in user, period.

= What does the Shortcode display when users are not logged in or their role is not allowed to delete themselves? =

Nothing, when using the self-closing Shortcode tag (i.e. `[plugin_delete_me /]`). However, when the opening and closing Shortcode tags are used (i.e. `[plugin_delete_me]`Content`[/plugin_delete_me]`), the content inside the tags will appear instead of the delete link.

= Where are users sent after deleting themselves? =

The **Settings -> Delete Me** subpanel lets you enter any URL you'd like to redirect deleted users to, set to homepage by default. You can leave "Landing URL" blank to have users remain at the same URL after deletion.

= Is there a confirmation before the user deletes themselves? =

Yes, the delete link on the **Your Profile** page leads to a pre-built confirmation page. You can optionally require users to confirm their password on this page before deletion. The Shortcode delete Link provides a Javascript confirm dialog [OK] [Cancel] by default, but may be disabled if preferred. Additionally, the Shortcode has a setting that provides a Form instead of the Link, which requires users to confirm their password.

= May I be notified of users who delete themselves and what was deleted? =

Yes. The **Settings -> Delete Me** subpanel has a setting called "E-mail Notification", just check the box and save changes.

= Does this plugin store any personal information about users? =

No, the only data stored is related to the plugin's settings which are located on the **Settings -> Delete Me** subpanel.

== Screenshots ==

1. **Your Profile** page.
2. **Your Profile** confirmation page. (This page is included out of the box)
3. Example of Shortcode Link. (Enable or disable the Javascript confirm dialog)
4. Example of Shortcode Form. (Create a page like this and send users to it for delete confirmation)
5. **Network Admin -> Settings -> Delete Me** subpanel. (Multisite installations only)
6. **Settings -> Delete Me** subpanel.

== Changelog ==

= 3.0 =

* Release date: 05/09/2020
* Added 3 new attributes for the shortcode form: form_confirm_warning, form_password_label, form_confirm_button
* Added %displayname% replacement string everywhere that %username% was already available.

= 2.9 =

* Release date: 04/05/2020
* Added paragraph tag around shortcode output to prevent alignment issues.

= 2.8 =

* Release date: 12/19/2018
* Added Role, First Name, and Last Name user data to email notification.

= 2.7 =

* Release date: 07/29/2018
* Bug fixed: Using a blank landing URL would cause a failure during redirect on some server configurations.
* Bug fixed: Landing URLs on the Settings page were incorrectly having http:// added to them when they did not contain a URL scheme.

= 2.6 =

* Release date: 05/25/2018
* Fixed 2 PHP parse errors affecting those using a PHP version less than 5.4.
* Added message for Administrators, in place of delete link (or form), to remind them the delete option configured is not visible to Administrators.

= 2.5 =

* Release date: 03/01/2018
* Multisite setting "Delete From Network" has been changed to be more intuitive. Users are now deleted from the entire Network regardless of the number of Sites to which they belong.
* Multisite setting "Delete From Network" is now unchecked by default.
* Multisite e-mail notifications now include the total number (if more than one) of Network Sites from which a user has been deleted whenever "Delete From Network" is checked.

= 2.4 =

* Release date: 02/16/2018
* Bug fixed: Added missing text domain to two translations.
* Removed invalid Plugin URI to comply with WordPress plugin Header Requirements
	
= 2.3 =

* Release date: 02/06/2018
* Added support for translation; a languages folder now contains a .POT file containing the English strings for translation.
* Removed wpml-config.xml to prevent conflicts and confusion between the database option and the newly added standard translation calls.
* Replaced donation link on Plugins page with link to Changelog.
* From this version forward, downgrading the plugin to a previous version will work but automatically resets settings to defaults.
* WordPress minimum required version changed from 3.4 to 3.7.

= 2.2 =

* Release date: 01/27/2018
* Bug fixed: Multisite installations with Network Wide enabled would not get selected role updates for roles of newly added Sites.
* Bug fixed: Error on user delete for WordPress versions less than 4.4.

= 2.1 =

* Release date: 01/26/2018
* Added Network Wide settings for Multisite installations that apply a single page of settings across all network Sites.
* Added a setting that requires users to confirm their password on the **Your Profile** confirmation page.
* Added a setting that allows inserting a form when using the shortcode instead of a link. This setting will also require users to confirm their password before deletion.
* Added "user_registered" (e.g. 2018-01-25 01:30:15) user data to the deleted user email notification.
* %sitename% can now be used for text replacement in the warning messages. The default warning messages have also been updated to include its use.

= 2.0 =

* Release date: 06/22/2016
* Applied init change discussed at https://wpml.org/forums/topic/delet-me-cant-be-translated/#post-919867
* Added wpml-config.xml file to allow easier use with the popular WPML plugin.

= 1.9 =

* Release date: 12/08/2015
* Fixed issue with using Shortcode multiple times on the same page or post.

= 1.8 =

* Release date: 07/15/2015
* The following new Shortcode attributes may be used to override settings, but are not required: class, style, html, js_confirm_warning, landing_url.
* **v1.7 change reverted** - %shortcode% term no longer used, attributes were added instead for a more complete and consistent way of customizing the shortcode.

= 1.7 =

* Release date: 07/14/2015
* Shortcode **Link** text can now contain **%shortcode%** which is replaced with the text inside the open and close shortcode tags. This was added to allow a dynamic way of changing the delete link text.

= 1.6 =

* Release date: 03/09/2015
* **Your Profile** delete link now leads to a customizable confirmation page instead of the Javascript confirm dialog.
* Added settings for **Your Profile** confirmation page Heading, Warning, and Button.
* Added setting to enable or disable Javascript confirm dialog for Shortcode delete link. This was added to make it easier to use a custom confirmation page with the Shortcode.

= 1.5 =

* Release date: 10/18/2014
* **Your Profile** and Shortcode "Landing URL" may now be left blank to remain at the same URL after deletion.
* Removed setting and code for "Uninstall on Deactivate". You can still wipe all traces of the plugin from the Plugins panel by deactivating and clicking Delete.
* Added button on settings page, "Restore Default Settings".
* Shortcode deletion link no longer relies on the get_permalink() function. This makes the shortcode's placement more flexible and the link location more accurate.
* wp_logout() function is now run after user deletion to cleanup session and auth cookies.
* Delete link default updated, old = "Delete Profile", new = "Delete Account".
* Javascript confirm text default updated, the line about Post and Links was removed.

= 1.4 =

* Release date: 04/24/2013
* Added setting to enable or disable the delete link on the **Your Profile** page.
* Added an uninstall.php file. This enables removal of the plugin capabilities and settings when you "Delete" the plugin from the `Plugins` panel in WordPress.
* Fixed possible PHP Warning: missing argument 2 `$wpdb->prepare()` on Multisite installations using WordPress 3.5+
* Fixed possible PHP Fatal error: undefined function `is_plugin_active_for_network()` on Multisite installations when adding a new Site from outside the WordPress Admin pages.
* Consolidated scripts to reduce the number of files used and the total plugin filesize.

= 1.3 =

* Release date: 04/23/2013
* Added setting to customize Javascript confirm text.

= 1.2 =

* Release date: 02/07/2013
* WordPress 3.4 now required.
* Added Multisite and Network Activation support.
* Added setting for Multisite to delete user from Network if user no longer belongs to any Network Sites.
* Added setting to delete comments.
* Edited e-mail notification to list the number of comments deleted.

= 1.1 =

* Release date: 04/11/2011
* Added setting for detailed e-mail notification when a user deletes themselves.
* Fixed undefined function errors for wp_delete_post and wp_delete_link when user has Posts or Links.

= 1.0 =

* Release date: 04/09/2011
* Initial release.

== Upgrade Notice ==

= 3.0 =

See [Changelog](https://wordpress.org/plugins/delete-me/#developers) for details.
