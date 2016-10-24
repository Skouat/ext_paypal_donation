# PayPal Donation for phpBB - Master Branch
This extension add a PayPal Donation page on your site.

[![Build Status](https://travis-ci.org/Skouat/ext_paypal_donation.svg?branch=master)](https://travis-ci.org/Skouat/ext_paypal_donation) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Skouat/ext_paypal_donation/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Skouat/ext_paypal_donation/?branch=master)

## Features
* PayPal IPN
    * Auto group
    * Donors list
    * Send PM to founders
    * Statistics
    * Transaction log
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
* Automatic redirection to the forum after a successfull/cancelled donation.
* Define and suggest a default donation value, or use a drop-down list.
* Admin and user permissions can be set through `ACP -> Permissions`.
* All options are manageable from ACP.

### Quick Install

1. [Download the latest validated release](https://www.phpbb.com/customise/db/extension/paypal_donation_extension/)
2. Unpack the downloaded release and copy it to the `ext` directory of your phpBB board.
3. Navigate in the ACP to `Customise -> Manage extensions`.
4. Look for `PayPal Donation` under the Disabled Extensions list, and click its `Enable` link.
5. Set up and configure PayPal Donation by navigating in the ACP to `Extensions -> PayPal Donation`.

## Uninstall

1. Navigate in the ACP to `Customise -> Extension Management -> Extensions`.
2. Look for `PayPal Donation` under the Enabled Extensions list, and click its `Disable` link.
3. To permanently uninstall, click `Delete Data` and then delete the `/ext/skouat/ppde` directory.

## Support

* **Important: Only official release versions validated by the phpBB Extensions Team should be installed on a live forum. Pre-release (beta, RC) versions downloaded from this repository are only to be used for testing on offline/development forums and are not officially supported.**
* Report bugs and other issues to the [PPDE Support Forum at phpBB.com](https://www.phpbb.com/customise/db/extension/paypal_donation_extension/) or [GitHub Issue Tracker](https://github.com/Skouat/ext_paypal_donation/issues).
* Support requests should be posted and discussed in the [PPDE Support Forum at phpBB.com](https://www.phpbb.com/customise/db/extension/paypal_donation_extension/).

## Translations

* Translations should be added on the [Transifex](https://www.transifex.com/skouat/ppde/) repository.
* Request a translator access by opening a ticket on the [Issue tracker](https://github.com/Skouat/ext_paypal_donation/issues/).

## License
[GNU General Public License v2](http://opensource.org/licenses/GPL-2.0)
