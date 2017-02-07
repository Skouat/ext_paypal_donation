# PayPal Donation for phpBB - Develop Branch
This extension add a PayPal Donation page on your site.

[![Build Status](https://travis-ci.org/Skouat/ext_paypal_donation.svg?branch=develop)](https://travis-ci.org/Skouat/ext_paypal_donation) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Skouat/ext_paypal_donation/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Skouat/ext_paypal_donation/?branch=develop)

## Features
* PayPal IPN
    * Auto group
    * Donors list
    * Notifications system
    * Statistics auto update
    * Transactions log
* Safely test this extension with PayPal Sandbox.
* Displaying Statistics donation on the Donation page and on the bottom of the forum index page
    * Progress bar
    * Donation received
    * Donation used
    * Goal to reach
* Advanced currencies management.
* Customize the main donation page, success page and cancel page.
    * You can use BBcode.
    * Multi-language customization available through the ACP
    * You can use predefined variables.
* Automatic redirection to the forum after a successful/cancelled donation.
* Define and suggest a default donation value, or use a drop-down list.
* Admin and user permissions can be set through `ACP -> Permissions`.
* All options are manageable from ACP.

### Quick Install

1. Unpack the downloaded release and copy files to the `ext/skouat/ppde` directory.
2. Navigate in the ACP to `Customise -> Manage extensions`.
3. Look for `PayPal Donation` under the Disabled Extensions list, and click its `Enable` link.
4. Set up and configure PayPal Donation by navigating in the ACP to `Extensions -> PayPal Donation`.

## Uninstall

1. Navigate in the ACP to `Customise -> Extension Management -> Extensions`.
2. Look for `PayPal Donation` under the Enabled Extensions list, and click its `Disable` link.
3. To permanently uninstall, click `Delete Data` and then delete the `/ext/skouat/ppde` directory.

## Support

* **Important: Only official release versions validated by the phpBB Extensions Team should be installed on a live forum. Pre-release (beta, RC) versions downloaded from this repository are only to be used for testing on offline/development forums and are not officially supported.**
* Report bugs and other issues in the [Issue Tracker](https://github.com/Skouat/ext_paypal_donation/issues).
* Support requests should be posted and discussed in the [PayPal Donation topic at phpBB.com](https://www.phpbb.com/community/viewtopic.php?f=456&t=2358616).

## Translations

* Translations should be added on the [Transifex](https://www.transifex.com/skouat/ppde-develop/) repository.
* Feel free to [join](https://www.transifex.com/signup/?join_project=ppde-develop) the translation team, and read this [Quick User Guide](/.tx/README.md)

## Install Extension
* Download the archive of the extension from here and unpack it. 
* Copy then the entire contents of the archive to your phpBB forum to /ext/skouat/ppde. 
* Now go into the admin area of the forum and chose the tab Customize, in the area Manage extensions is you now the extension PayPal Donation displayed under the disable extensions. 
* Click behind the extension on the link Enable to activate the extension. After you have confirmed the action the extension is available in your forum.

## License
[GNU General Public License v2](http://opensource.org/licenses/GPL-2.0)
