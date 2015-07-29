# PayPal Donation for phpBB

This extension add a PayPal Donation page on your site.

Master branch  
[![Build Status](https://travis-ci.org/Skouat/ext_paypal_donation.svg?branch=master)](https://travis-ci.org/Skouat/ext_paypal_donation) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Skouat/ext_paypal_donation/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Skouat/ext_paypal_donation/?branch=master)

Develop branch  
[![Build Status](https://travis-ci.org/Skouat/ext_paypal_donation.svg?branch=develop)](https://travis-ci.org/Skouat/ext_paypal_donation) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Skouat/ext_paypal_donation/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Skouat/ext_paypal_donation/?branch=develop)

## Features
* PayPal IPN
    * Auto group
    * Transaction log
    * Send PM to founders
    * Statistics
* Safely test this extension with PayPal Sandbox
* Displaying Statistics donation on the Donation page and on the bottom of the forum index page :
    * Progress bar
    * Donation received
    * Donation used
    * Goal to reach
* Advanced currencies management :
    * Add/remove/enable/disable
    * Backup currency
    * I hope no, but if you remove or disable all currencies, a language key is defined to provide a backup currency.
      If the language key does not exist, U.S. Dollar will be defined as the default currency.
* Customize the main donation page, success page and cancel page.
    * You can use BBcode.
    * Multi-language customization available through the ACP
    * You can use predefined variables.
* Automatic redirection to the forum after a successfull/cancelled donation.
* Define and suggest a default donation value, or use a drop-down list.
* Admin and user permissions can be set through ACP >> Permissions.
* All options are manageable from ACP.

## Styles
* Prosilver

## License
[GNU General Public License v2](http://opensource.org/licenses/GPL-2.0)
