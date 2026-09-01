# Changelog

All notable changes to this project are documented in this file.

This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).
Within each release, entries are grouped by type (Add, Change, Remove, Fix)
and the releases are listed in reverse chronological order.

## 4.0.3 - 2026-09-01
- Fix: Property `auth` not found in overview_controller

## 4.0.2 - 2026-08-29

- Change: Consolidate admin-context and permission checks into the auth actions class
- Change: Show the quote BBCode button in the donation pages ACP editor
- Fix: Require a link hash to enable or disable a currency in the ACP
- Fix: Require a form key on the manual transaction add and donor change forms in the ACP
- Fix: Move the hardcoded cURL availability message from `client_factory` to the language files
- Fix: Show the image and URL BBCode buttons in the donation pages ACP editor

## 4.0.1 - 2026-07-26

- Change: Replace magic numbers with named constants in `order_controller`
- Change: Code clean-up in the webhook listener and donation recorder (clearer naming, docblocks)
- Fix: Log webhook order lookup failures in the admin log for easier troubleshooting
- Fix: Enforce ACL check before displaying the confirm box in the Overview ACP module
- Fix: Avoid a PHP notice when the board name contains emoji or CJK characters in the PayPal soft descriptor

## 4.0.0 - 2026-07-05

- Add: Migration from PayPal IPN to the PayPal REST API (Orders API v2 + JS SDK)
- Add: Webhook listener to record donations, with a synchronous capture fallback if the webhook is unreachable
- Add: Webhook handling for pending and denied captures (plus refunds/reversals)
- Add: Unique `txn_id` index for idempotency
- Add: REST API settings, "Test connection" button and webhook URL in PayPal Features
- Add: New `skouat.ppde.donors_group_user_remove_before` event
- Add: New `{DONATION_USED}` predefined variable for the donation pages
- Add: Default message on the success/cancel pages when no content is configured
- Add: docs/csp.md documenting the required CSP directives
- Change: Now requires phpBB 3.3.11+, PHP 7.2+ and ext-openssl
- Change: Currency formatting follows each user's language (new "Automatic" locale option)
- Change: Notifications and auto group each have their own toggle
- Change: Improved PayPal checkout page (label, brand name, no shipping)
- Change: Modernise donation progress bars (smoother colours, accessibility, RTL)
- Change: `skouat.ppde.do_actions_completed_before` now fires from the donation recorder
- Change: Errors logged in the admin log; Overview reports the OpenSSL version
- Change: Rename internal IPN methods to Sandbox (deprecated aliases kept)
- Change: Global code review and clean-up (trim docblocks, remove redundant comments)
- Remove: PayPal IPN listener, postback, TLS/cURL detection and obsolete configs
- Remove: Obsolete "donation with errors" approval workflow and notification
- Fix: Donors list sorting under strict SQL mode (ONLY_FULL_GROUP_BY)
- Fix: Undefined array key warning when formatting a transaction with no settlement currency
- Fix: Preserve the exact net amount returned by PayPal instead of recomputing it (gross − fee)
- Fix: Donors list error when a donor's last transaction is concurrently removed
- Fix: prevent import() from permanently mutating the entity schema
- Fix: Donors list SQL portability, last-donation date accuracy and N+1 queries
- Fix: Donors list heading counting (donor, currency) pairs instead of distinct donors
- Fix: Wrong heading displayed on the donation success/cancel pages
- Fix: Add a missing accessible label to the donation amount field

## 3.0.4 - 2021-04-20

- Change: Global code review
- Fix: Merchant ID does not match
- Fix: Undefined index on transactions module

## 3.0.3 - 2021-03-13

- Change: Improve processing of transaction data in IPN listener
- Change: Add htmlspecialchars_decode() for username items in notifications systems
- Change: Move post_data functions on its own actions controller
- Fix: Check for the existence of the auth_admin class was not properly set
- Fix: Typo on template vars name
- Fix: The amount received always set to 0 in email notifications (#92)
- Fix: Remove use of phpbb_email_hash()
- Fix: Undefined index when deleting a transaction

## 3.0.2 - 2021-01-08

- Change: Code changes following phpBB Customisation Team reports
- Fix: Class 'ResourceBundle' not found
- Fix: Travis-ci reported issues

## 3.0.1 - 2020-12-12

- Add: Position of donation stats can be defined
- Add: Add options to set guest permissions from PDDE settings
- Fix: Access array offset on value of type null
- Fix: Get property 'tls_version' of non-object

## 3.0.0 - 2020-03-17

- Add: Implement support money format, based on PHP intl extension
- Add: Donation stats bars can be disabled to show only text information
- Add: New predefined variables are available for Donation pages
- Change: Re-enable TLS check
- Change: In donors list, group donations by username and currencies
- Change: Code review of notification system
- Change: Convert email template vars to Twig
- Change: Update datepicker to 1.0.9
- Change: Code improvement
- Fix: Transactions detected as invalid if PayPal memo contains specials chars

## 2.1.5 - 2020-12-16

- Change: Quick code clean-up
- Fix: Typo
- Fix: Invalid lang keys in email templates

## 2.1.4 - 2020-05-03

- Fix: SQL error on transactions log

## 2.1.3 - 2020-01-25

- Change: Code changes following phpBB Customisation Team reports

## 2.1.2 - 2019-11-19

- Change: Temporarily disable TLS check because PayPal TLS website is down
- Change: Add `payer_donated_amount` on `donors_group_user_add()` event (thanks Dark❶)
- Remove: Remove abilities to enable/disable text formatting on Donation Pages
- Fix: Auto group feature does not work (#75)
- Fix: SQL Error when accessing on list of donors (#78)
- Fix: English wording

## 2.1.1 - 2019-05-26

- Change: Code changes following phpBB Customisation Team reports
- Fix: The button `Add` for adding manual transaction is not visible when logs are empty

## 2.1.0 - 2019-05-09

- Add: Donation on error can be manually approved
- Add: Allow changing transactions donor (thanks kasimi)
- Change: Make donor name a link to the profile when viewing a transaction (#61) (thanks kasimi)
- Change: Use built-in phpbb_email_hash() function (#59) (thanks kasimi)
- Change: Major code improvement/clean-up
- Fix: All transactions log are cleared when "Delete marked" is used
- Fix: Allow modification of PayPal remote hosts
- Fix: Template system returns error when multiple styles are enabled
- Fix: Use square brackets for array access (#62) (thanks kasimi)
- Fix: Use singular form of Donor (#60) (thanks kasimi)

## 2.0.1 - 2018-10-22

- Add: PayPal Postdata check and error tracking
- Add: Memo field in transactions log view
- Add: Multiple checks on PayPal returned variables
- Change: Enhance CSS compatibility with other styles (thanks Mazeltof)
- Change: Improve log error
- Change: Refactor IPN Listener
- Change: Use the same name for the extension display name and the contribution in the phpBB CDB
- Change: Code improvement
- Fix: Adjust some columns size on database
- Fix: CSS code style
- Fix: JS vars not escaped in template
- Fix: Invalid call for some Langkeys
- Fix: Invalid operator usage in some PHP condition
- Fix: Missing revert schema in migration
- Fix: Missing ACP root path in some `append_sid()`
- Fix: Remove use of deprecated `$user->lang`
- Fix: Services injection
- Fix: Smilies are selectable on Donation Page Management only when preview mode is used
- Fix: The transaction ID was not coloured in Red when transaction status was not "Completed"

## 2.0.0 - 2018-10-02

- Add: Minimum amount before auto group donors (#40)
- Change: Improve transaction debug
- Change: Migrate extension to be compatible with phpBB 3.2
- Change: Refactor language files
- Change: Refactor ACP Overview module
- Change: Refactor IPN module
- Change: Update PayPal prerequisite checks (TLS 1.2, HTTP/1.1)
- Change: Update PayPal IPN Verification Postback to HTTPS
- Change: Use Twig syntax in all template files
- Change: Use HTML5 tags instead of xHTML Strict
- Change: Update Transifex config and Readme files
- Change: Code improvement
- Remove: Extension version check removed from the Overview module
- Remove: fsockopen related code
- Fix: Undefined offset when the default currency is disabled
- Fix: In the donation page, the default donation value is not selected in the dropdown menu
- Fix: Prevent Transaction user_id to be set to 0 (#41)
- Fix: Missing language keys
- Fix: "ppde_first_start" not set properly after first start of PPDE
- Fix: Fails to get extension metadata after upgrading to phpBB 3.2.1 (#47)
- Fix: Some SQL result wasn't freed
- Fix: English wording (thanks kasimi)
- Fix: Unable to use "delete all" in Transactions Log module

## 1.0.3 - 2017-01-22

- Add: Add extension events. More information in `/docs/events.md` (Thanks kasimi)
- Add: The default donation value becomes the default value in the dropdown list. If it's not present, the value is
  added to the dropdown list
- Change: PPDE links moved before the link "FAQ" in the header navbar
- Change: Remove unnecessary input and label attributes
- Change: Move PayPal IPN Features on its own ACP module
- Change: Donation statistics display float value
- Change: Use a unique filename for the transaction logfile
- Change: Code enhancement
- Fix: Protect the move actions with hash
- Fix: Donors list displays a HTML bullet near of pagination when there is no donors
- Fix: Remove unused language keys
- Fix: Fix HTML tags in ACP transactions log
- Fix: Method without return statement
- Fix: Check the PHP version before activating the extension
- Fix: Failed to enable extension after disabled

## 1.0.2 - 2016-08-08

- Change: Add the type of parameter into the method declaration (Thanks ErnadoO)
- Change: Hide/Show ACP IPN Features using jQuery (Thanks cabot)
- Fix: Disabling of currency is enhanced by usage of AJAX, but change is not reflected directly
- Fix: Duplicate entries in Transaction Log when status returned by PayPal is different from "completed"
- Fix: Donation statistics on index are displayed even if there is no content
- Fix: IPN listener was unable to works if "Sandbox only for founder" was enabled
- Fix: Missing CSRF check on delete process
- Fix: Remove use of `include_once()`
- Fix: "Sandbox only for founder" always displayed as enabled even if it was disabled
- Fix: Use `is_set()` method in `$request` instead of use `!isset()` on Super Global
- Fix: Wrong value on xHTML `<input>` `disabled` attribute

## 1.0.1 - 2016-05-09

- Add: Convert/purge old data from PayPal Donation MOD 1.0.4
- Fix: IN_PHPBB is not defined in `/skouat/ppde/controller/ipn_listener.php`
- Fix: Error with migration file during installation

## 1.0.0 - 2016-05-08

- First release (not published)
