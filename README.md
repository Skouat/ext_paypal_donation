# PayPal Donation for phpBB - 4.0.x Branch
This extension adds a PayPal Donation page on your site.

[![Build Status](https://github.com/Skouat/ext_paypal_donation/workflows/Tests/badge.svg)](https://github.com/Skouat/ext_paypal_donation/actions) [![codecov](https://codecov.io/gh/Skouat/ext_paypal_donation/branch/4.0.x/graph/badge.svg?token=YEdsDRUQWg)](https://codecov.io/gh/Skouat/ext_paypal_donation) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Skouat/ext_paypal_donation/badges/quality-score.png?b=4.0.x)](https://scrutinizer-ci.com/g/Skouat/ext_paypal_donation/?branch=4.0.x) [![Crowdin](https://badges.crowdin.net/skouat-ppde/localized.svg)](https://crowdin.com/project/skouat-ppde)

## Features
  * PayPal REST API integration
    * Auto group
    * Donors list
    * Notifications system
    * Statistics auto update
    * Transactions log
  * Add Manual Transaction (since 2.1.0)
  * Safely test this extension with PayPal Sandbox.
  * Display Statistics donation on the Donation page and on the bottom of the forum index page
    * Progress bar
    * Donation received
    * Donation used
    * Goal to reach
  * Advanced currencies management.
  * Customize the main donation page, success page and cancel page.
    * Multi-language customization available through the ACP
    * BBcode usage available
    * Predefined variables
  * Automatic redirection to the forum after a successful/cancelled donation.
  * Define and suggest a default donation value, or use a drop-down list.
  * Admin and user permissions can be set through `ACP -> Permissions`.
  * All options are manageable from ACP.

## Quick Install
  1. Unpack the downloaded release and copy files to the `ext/skouat/ppde` directory.
  2. Navigate in the ACP to `Customise -> Manage extensions`.
  3. Look for `PayPal Donation` under the Disabled Extensions list, and click its `Enable` link.
  4. Set up and configure PayPal Donation by navigating in the ACP to `Extensions -> PayPal Donation`.
  5. If your board enforces a Content Security Policy, see [docs/csp.md](docs/csp.md) to allow PayPal's domains.

## Upgrading to 4.0.0 (PayPal IPN → REST API)

Versions prior to 4.0.0 relied on PayPal IPN, which is now **deprecated by PayPal** and no longer usable for this
donation flow. As a result, **donations no longer work on earlier versions**. Version 4.0.0 switches to the **PayPal
REST API**, which restores donations and **removes the requirement to use a charity/non-profit PayPal account**.
Upgrading is therefore required. After updating the files, follow these steps to get donations working again:

  1. **Update requirements:** make sure your board runs **phpBB 3.3.11+**, **PHP 7.2+** and that the PHP **`openssl`** and **`curl`** extensions are enabled.
  2. **Create a REST API app:**
      - Go to the [PayPal Developer Dashboard](https://developer.paypal.com/dashboard/applications/live) → **Apps & Credentials**.
      - Create an app (or open an existing one) for **Live**, and note its **Client ID** and **Secret**. Repeat under the **Sandbox** tab if you want to test.
  3. **Create a webhook:**
    - In the ACP, go to `Extensions -> PayPal Donation -> PayPal Features` and copy the **Webhook URL** displayed there.
    - In the PayPal Developer Dashboard, open your app and add a webhook using that URL, subscribed to the following events:
      - **“Payment capture completed”** (`PAYMENT.CAPTURE.COMPLETED`) — records donations
      - **“Payment capture refunded”** (`PAYMENT.CAPTURE.REFUNDED`) — tracks refunds
      - **“Payment capture reversed”** (`PAYMENT.CAPTURE.REVERSED`) — tracks chargebacks/reversals
      - **“Payment capture pending”** (`PAYMENT.CAPTURE.PENDING`) — tracks payments awaiting settlement (e.g. card payments)
      - **“Payment capture denied”** (`PAYMENT.CAPTURE.DENIED`) — tracks declined captures
    - Copy the generated **Webhook ID**. Repeat for the Sandbox app if needed.
  4. **Enter the credentials in the ACP:** in `PayPal Features`, fill in the **Client ID**, **Secret** and **Webhook ID** for Live (and Sandbox if used), then save.
  5. **Test the connection:** use the **“Test connection”** button to confirm your credentials are valid.
  6. **Content Security Policy:** if your board enforces a CSP, update it to allow PayPal's domains (see [docs/csp.md](docs/csp.md)).

> **Note:** The legacy “PayPal account ID” (email/Merchant ID) under *General Settings* is no longer used to process payments; the REST API relies on the Client ID/Secret above.

## Uninstall
  1. Navigate in the ACP to `Customise -> Extension Management -> Extensions`.
  2. Look for `PayPal Donation` under the Enabled Extensions list, and click its `Disable` link.
  3. To permanently uninstall, click `Delete Data` and then delete the `/ext/skouat/ppde` directory.

## Support
  * **Important: Only official release versions validated by the phpBB Extensions Team should be installed on a live forum. Pre-release (beta, RC) versions downloaded from this repository are only to be used for testing on offline/development forums and are not officially supported.**
  * Report bugs and other issues in the [Issue Tracker](https://github.com/Skouat/ext_paypal_donation/issues).
  * Support requests should be posted and discussed in the [PayPal Donation topic at phpBB.com](https://www.phpbb.com/community/viewtopic.php?f=456&t=2358616).

## Translations
  * This project use [Crowdin](https://crwd.in/skouat-ppde) for translations.  
    Feel free to [join](https://crwd.in/skouat-ppde) the project, and read this [Quick Translator Guide](https://github.com/Skouat/ext_paypal_donation/blob/develop-3.3.x/docs/crowdin.md).
  * You can also send your translations in the [PayPal Donation translation topic at phpBB.com](https://www.phpbb.com/customise/db/extension/paypal_donation_extension/support/topic/216046).

## License
[GNU General Public License v2](https://opensource.org/licenses/GPL-2.0)
