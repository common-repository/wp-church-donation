=== wp-church-donation ===
Contributors: ashishajani
Donate link: http://freelancer-coder.com
Tags: church, donation, donate to church, wp church donation, authorize.net, credit card payment, donate to church, transfer, charge, donate plugin, church donation form, recursive donation, recent donor information, donation, donations, charity, wordpress donation plugin, wordpress plugin, wordpress, online giving, online giving wordpress plugin, online giving plugin, wp online giving plugin
Requires at least: 5.8
Tested up to: 6.4.3
Requires PHP: 7.4.0
Stable tag: 1.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

With Church Donation plugin, church websites built with WP can accept donations from sponsors, donors, members, fans and supporters via credit card.

== Description ==

Online giving is a big reason to have a church websites and developing website with WordPress is simple and great solution because it is free, development cost is lower and most importantly there are very large amount of ready-made free as well as paid themes available for Church websites which downgrade the development cost as well as time.

Online giving or we can say receiving donation online is the prime focus of Church websites because now a days majority of donations are made online. WP Church Donation plugin helps church websites for this. 

Authorize.net is most widely used payment gateway to process payments online and accepts Visa, MasterCard, Discover and other variants of cards and best option to receive payment online. Install WP Church Donation Plugin and get started to accept donations through Authorize.net with ease.

WP Church Donation plugin features:

*  Integration of Authorize.Net payment gateway to receive donation.
*  Accept donation using Credit Card.
*  Easy to use Authorize.Net payment gateway settings options at administrator area.
*  Place donation form anywhere on the site using ShortCode.
*  List of donors along side with details for particular donation and donors at administrator area.
*  Facility to export donation and donors list for administrators.
*  Email notifications to donors and site owner(administrator) when donation made.
*  Easy management of content used in email notification.
*  Dynamic thank you page content with editable content at administrator area.
*  Allowed custom donation amount.
*  Form validations for both client side(using jQuery) and server side(using PHP).


For further information on plugin, suggestion or comments on how to customize the plugin please drop me a contact request from [http://freelancer-coder.com](http://freelancer-coder.com). If I will not able to provide complete support I will make sure that I will provide guidelines or some useful information for the addressed situation.

== Installation ==

Installation process is very simple for WP Church Plugin. Ways to install plugin:

= Installation with FTP: =

      1. Download wp-church-donation plugin.
      2. Extract plugin.
      2. Upload wp-church-donation directory to the '/wp-content/plugins/' directory.
      3. Go to Plugins option from left menu and activate 'wp-church-donation' plugin from the list.
      
= Installation with Upload method via WordPress admin panel: =

      1. Download wp-church-donation plugin.
      2. Go to plugins page by clicking on Plugins menu item from left menu.
      3. Click on 'Add New' option.
      4. Upload the 'plugin and activate.

= How to configure Authorize.net payment gateway: =

      1. After activating the plugin, click on 'Auth.net Settings' menu option under 'Church Donation' from left menu.
      2. Enter your Authorize.net API credentials including 'Login ID' and 'Transaction Key'.
      3. There is a checkbox to enable test mode of Authorize.net, check to enable test mode and uncheck to enable live mode.
      4. Save settings.

= How to customize email content, thank you message and description provided on the top of the donation form: =

      1. After activating the plugin, click on 'Content' menu option under 'Church Donation' from left menu.
      2. It will show inputs to add Donation page header and message, thank you message, email notification subject and content.
      3. Make changes according to your need.
      4. Submit.
   
= How to keep track on received donations list and manage it: =

      1. Click on 'Church Donation' from left menu.
      2. You will see a list of donation entries.
      3. Click on view details link to see all the details related to that donation entry.
      4. Click on delete to remove the entry from list.
      5. Click on Export link at the top of listing to export all the entries in CSV format.

= How to use WP Church Donation plugin at client side: =

      1. Place `[Show Church Donation Form]` or `[Show_Church_Donation_Form]` in your content or `<?php echo wp_church_donation_form(); ?>` in your template.

== Frequently Asked Questions ==
= Support available? =
No the support is not available for this plugin but If you need any modification in plugin or need some extra functionality than please let me know [here](http://freelancer-coder.com). I will try to help you by providing enough guidelines on how to make changes in this plugin.

= Will it work on my Theme? =
WP-Church-Donation features an inline donation form so once you will activate plugin it will be shown on any template. Then after you can make changes in CSS file according to your requirements. CSS for this form resides at 'wp-church-donation/css/wp-church-donation-form.css'.

= Can I expand this plugin =
Yes you can customize or expand plugin by adding new payment gateways to receive donation or customization can be made in form related to fields.

= About SSL =
In order to process transactions in a secure manner, you need to [purchase an SSL Certificate](http://www.noeltock.com/sslcertificates/). This way consumers can purchase/donate with confidence. There are multiple plugins for then enforcing that SSL be used on your page, [here's one](http://wordpress.org/extend/plugins/wordpress-https/).

= What are limitations of this plugin? =
It is only available to users with Authorize.Net payment gateway to receive donations.

= I've noticed a bug, what should I do now? =
This is a very first release of the plugin, so bugs are predicted to show up. It would be great if you could send me details [http://freelancer-coder.com](http://freelancer-coder.com).

== Screenshots ==
1. Donation form at frontend with information including message at the top, donation information and donor information.
2. List of donations received at admin area.
3. Details of individual donation entry.
4. Authorize.Net payment gateway settings page.
5. Management of content including message at the top of donation page, thank you email and thank you message.
6. Email sent to admin with the donation details.
7. Email sent to donor with the donation details.

== Changelog ==
= 1.0 =
Initial plugin upload
= 1.1 =
* Added pagination for donation entries(20 records per page) at admin area. 
* Modified date format in listings.
= 1.2 =
* Fixed short code issue for new WordPress versions.
= 1.3 =
* Resolved conflicts occurred for latest WP version, now it allows adding entries in DB. 
* Modified API URLs according to latest Akamai changes made by Auth.net. 
* Included one more field named category in donation form.
= 1.5 =
* Updated Authorize.Net SDK.
* Resolved header already sent warning.
* Tested plugin with latest WordPress version (4.7.5). 
* Passed phone number in Authorize.Net payment calls.
= 1.6 =
* Removed non-GPL Authorized.net library.
* Upgraded plugin to process payment via direct APIs.
= 1.7 =
* Tested plugin with latest WordPress version (6.4.3)
* Updated API methods to XML-based API for secure payment processing.
== Upgrade Notice ==
* 1.0 to 1.1 upgrade will not make any changes to existing features, just added pagination for donors list at admin area.
* 1.1 to 1.2 Upgrade will allow placing shortcodes with space and _ e.g. `[Show Church Donation Form]` or `[Show_Church_Donation_Form]`
* 1.2 to 1.3 Authorize.Net is now using Akamai to optimize Internet traffic routing, which includes your transaction requests. Using Akamai is currently optional, but will be mandatory for all merchants on June 30th 2016.
* 1.3 to 1.5 Upgraded Authorize.Net SDK and made some other changes, please do take backup before upgrading the plugin.
* 1.5 to 1.6 Upgraded plugin to remove non-GPL Authorize.net library and implemented direct API to process payment.
* 1.6 to 1.7 Upgrade plugin to update the process payment securly and based on XML.
