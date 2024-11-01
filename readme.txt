=== Zoho ZeptoMail for WooCommerce ===
Contributors: ZeptoMail
Tags: mail,transactional email,zoho zeptomail,woo, woocommerce
Donate link: none
Requires at least: 4.8
Tested up to: 6.6
Requires PHP: 5.6
Stable tag: 1.0.5
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

ZeptoMail Plugin lets you configure your ZeptoMail account on your wordpress site enabling you to send transactional emails of your site via ZeptoMail API.

== Description ==

= Zoho ZeptoMail for WooCommerce =

The ZeptoMail plugin helps you to configure your ZeptoMail account in your WooCommerce site, to send notification emails from your website.

== PRE-REQUISITES ==
- A ZeptoMail Account
- A self-hosted WordPress account
- WooCommerce plugin installed

== ZeptoMail API ==
You can use the OAuth token of your ZeptoMail account to send transactional emails from your site using ZeptoMail API.

== INSTALLATION ==
1) Login to your WooCommerce account. In the plugins section, search for the  "ZeptoMail" plugin.
2) Click Install now.
3) To configure your account, you need to add and generate the Client id and Client secret (credentials) parameters from your Zoho account. Navigate to this link ( https://api-console.zoho.com/)
4) Enter your server details and the authorization URL available in the plugin installation page.
5) Once you enter these details, you will be able to generate the client id and client secret parameters. Add these to the plugin installation section.
6) Once done, you will be taken to the Authorization page(https://accounts.zoho.com) where you should allow ZeptoMail to access your data. With this, the configuration setup will be done and you can start using the plugin.

== ZeptoMail PLUGIN PARAMETERS ==
- **Client Id** :The public identifier for your application.
- **Client secret** : Confidential key used to authenticate your application.
- **From Email Address** :The Email address that will be used to send all the outgoing transactional emails from your website.
- **From Name** :The Name that will be shown as the display name while sending all emails from your website.

== Frequently Asked Questions ==

1) **What is ZeptoMail?**

ZeptoMail is a transactional email sending service by Zoho Mail. This includes emails triggered by user action on your website or application like password reset emails, welcome emails, order confirmation emails etc. Having installed WordPress, if the PHP wp_mail() function isn't working or if your notification emails are sent to spam, ZeptoMail is the service to fix these issues.

2) **Is ZeptoMail free?**
   ZeptoMail service and this plugin are free to get started with. We provide you with 10000 free emails on sign up. If you need to send more emails, you can buy credits from your account. The pay-as-you-go plan and ensures you only pay for what you use. Find out more ZeptoMail pricing.


4) **Can I link more than one Mail Agent with the plugin?**

As of now, you can only configure one ZeptoMail Mail Agent with the plugin. You can only send emails through the chosen Mail Agent and its associated domain, using the plugin.

5) **Is the bounce address same as From address?**

No. During the plugin configuration, you will need to enter the From address and bounce address. The From address can be any email address belonging to the domain you have associated with the Mail Agent in the plugin. Bounce address is the bounce email address you have configured for the chosen Mail Agent in ZeptoMail. [Learn more](https://www.zoho.com/zeptomail/help/bounce-address.html)

6) **Where do I go for more assistance with ZeptoMail plugin?**

You can refer our help documentation for detailed instruction about ZeptoMail and the plugin. If you require further assistance, feel free to contact support@zeptomail.com with your questions.

== Screenshots ==
1. Configure Account(screenshot-1.png)

== Changelog ==
= 1.0.0 =
* Initial changes
= 1.0.1 =
* Removed unused file
= 1.0.2 =
* plugin name changed
= 1.0.3 =
* hard-code removed
= 1.0.4 =
* user agent removed
= 1.0.5 =
* multi dc added and domain url changes
== Upgrade Notice ==
none


