# Changelog
## 3.0.5 - 2023-07-16
  - Fix: SQL error on donors list when SQL modes is set to `ONLY_FULL_GROUP_BY` 
  - Change: Remove JS cdata and type (thanks to cabot)
  - Code refactoring

## 3.0.4 - 2021-04-20
  - Fix: Merchant ID does not match
  - Fix: Undefined index on transactions module
  - Global code review

## 3.0.3 - 2021-03-13
  - Fix: Check for the existence of the auth_admin class was not properly set
  - Fix: Typo on template vars name
  - Fix: The amount received always set to 0 in email notifications (#92)
  - Fix: Remove use of phpbb_email_hash()
  - Fix: Undefined index when deleting a transaction
  - Change: Improve processing of transaction data in IPN listener
  - Change: Add htmlspecialchars_decode() for username items in notifications systems
  - Change: Move post_data functions on its own actions controller

## 3.0.2 - 2021-01-08
  - Fix: Class 'ResourceBundle' not found
  - Fix: Travis-ci reported issues
  - Code changes following phpBB Customisation Team reports

## 3.0.1 - 2020-12-12
  - Add: Position of donation stats can be defined
  - Add: Add options to set guest permissions from PDDE settings
  - Fix: Access array offset on value of type null
  - Fix: Get property 'tls_version' of non-object

## 3.0.0 - 2020-03-17
  - Add: Implement support money format, based on PHP Intl extension
  - Add: Donation stats bars can be disabled to show only text information
  - Add: New predefined variables are available for Donation pages
  - Fix: Transactions detected as invalid if PayPal memo contains specials chars
  - Change: Re-enable TLS check
  - Change: In donors list, group donations by username and currencies
  - Change: Code review of notification system
  - Change: Convert email template vars to Twig
  - Change: Update datepicker to 1.0.9
  - Code improvement

## 2.1.5 - 2020-12-16
  - Fix: Typo
  - Fix: Invalid lang keys in email templates
  - Quick code cleanup

## 2.1.4 - 2020-05-03
  - Fix: SQL error on transactions log

## 2.1.3 - 2020-01-25
  - Code changes following phpBB Customisation Team reports

## 2.1.2 - 2019-11-19
  - Change: Temporarily disable TLS check because PayPal TLS website is down  
  - Change: Add `payer_donated_amount` on `donors_group_user_add()` event (thanks to Dark❶)
  - Fix: Auto group feature does not work (#75)
  - Fix: SQL Error when accessing on list of donors (#78)
  - Fix: English wording 
  - Remove: Remove abilities to enable/disable text formatting on Donation Pages

## 2.1.1 - 2019-05-26
  - Fix: The button `Add` for adding manual transaction is not visible when logs are empty
  - Code changes following phpBB Customisation Team reports

## 2.1.0 - 2019-05-09
  - Add: Donation on error can be manually approved
  - Add: Allow changing transactions donor (thanks to kasimi)
  - Change: Make donor name a link to the profile when viewing a transaction (#61) (thanks to kasimi)
  - Change: Use built-in phpbb_email_hash() function (#59) (thanks to kasimi)
  - Fix: All transactions log are cleared when "Delete marked" is used
  - Fix: Allow modification of PayPal remote hosts
  - Fix: Template system returns error when multiple styles are enabled
  - Fix: Use square brackets for array access (#62) (thanks to kasimi)
  - Fix: Use singular form of Donor (#60) (thanks to kasimi)
  - Major code improvement/cleanup

## 2.0.1 - 2018-10-22
  - Add: PayPal Postdata check and error tracking
  - Add: Memo field in transactions log view
  - Add: Multiple checks on PayPal returned variables
  - Change: Enhance CSS compatibility with other styles (thanks to Mazeltof)
  - Change: Improve log error
  - Change: Refactor IPN Listener
  - Change: Use the same name for the extension display name and the contribution in the phpBB CDB
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
  - Fix: The transaction ID was not colored in Red when transaction status was not "Completed"
  - Code improvement

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
  - Fix: Undefined offset when the default currency is disabled
  - Fix: In the donation page, the default donation value is not selected in the dropdown menu
  - Fix: Prevent Transaction user_id to be set to 0 (#41)
  - Fix: Missing language keys
  - Fix: "ppde_first_start" not set properly after first start of PPDE
  - Fix: Fails to get extension metadata after upgrading to phpBB 3.2.1 (#47)
  - Fix: Some SQL result wasn't freed
  - Fix: English wording (thanks to kasimi)
  - Fix: Unable to use "delete all" in Transactions Log module
  - Remove: Extension version check removed from the Overview module
  - Remove: fsockopen related code
  - Translation: Update Transifex config and Readme files
  - Code improvement

## 1.0.3 - 2017-01-22
  - Add: Add extension events. More information in `/docs/events.md` (thanks to kasimi)
  - Add: The default donation value becomes the default value in the dropdown list. If it's not present, the value is added to the dropdown list
  - Change: PPDE links moved before the link "FAQ" in the header navbar
  - Change: Remove unnecessary input and label attributes
  - Change: Move PayPal IPN Features on its own ACP module
  - Change: Donation statistics display float value
  - Change: Use a unique filename for the transaction logfile
  - Fix: Protect the move actions with hash
  - Fix: Donors list displays a HTML bullet near of pagination when there is no donors
  - Fix: Remove unused language keys
  - Fix: Fix HTML tags in ACP transactions log
  - Fix: Method without return statement
  - Fix: Check the PHP version before activating the extension
  - Fix: Failed to enable extension after disabled
  - Code enhancement

## 1.0.2 - 2016-08-08
  - Fix: Disabling of currency is enhanced by usage of AJAX, but change is not reflected directly
  - Fix: Duplicate entries in Transaction Log when status returned by PayPal is different from "completed"
  - Fix: Donation statistics on index are displayed even if there is no content
  - Fix: IPN listener was unable to works if "Sandbox only for founder" was enabled
  - Fix: Missing CSRF check on delete process
  - Fix: Remove use of `include_once()`
  - Fix: "Sandbox only for founder" always displayed as enabled even if it was disabled
  - Fix: Use `is_set()` method in `$request` instead of use `!isset()` on Super Global
  - Fix: Wrong value on xHTML `<input>` `disabled` attribute
  - Change: Add the type of parameter into the method declaration (thanks to ErnadoO)
  - Change: Hide/Show ACP IPN Features using jQuery (thanks to cabot)

## 1.0.1 - 2016-05-09
  - Fix: IN_PHPBB is not defined in `/skouat/ppde/controller/ipn_listener.php`
  - Fix: Error with migration file during installation
  - Add: Convert/purge old data from PayPal Donation MOD 1.0.4

## 1.0.0 - 2016-05-08
  - First release (not published)
