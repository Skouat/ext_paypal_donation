# Changelog

## 2.2.0 - 2019-05-12
  - Add: Implement support money format, based on PHP Intl extension.

## 2.1.1 - 2019-05-26
  - Fix: The button `Add` for adding manual transaction is not visible when logs are empty
  - Code changes following phpBB Customisation Team reports

## 2.1.0 - 2019-05-09
  - Add: Donation on error can be manually approved
  - Add: Allow to change transactions donor (thanks kasimi)
  - Change: Make donor name a link to the profile when viewing a transaction (#61) (thanks kasimi)
  - Change: Use built-in phpbb_email_hash() function (#59) (thanks kasimi)
  - Fix: All transactions log are cleared when "Delete marked" is used
  - Fix: Allow modification of PayPal remote hosts
  - Fix: Template system returns error when multiple styles are enabled
  - Fix: Use square brackets for array access (#62) (thanks kasimi)
  - Fix: Use singular form of Donor (#60) (thanks kasimi)
  - Major code improvement/cleanup

## 2.0.1 - 2018-10-22
  - Add: PayPal Postdata check and error tracking
  - Add: Memo field in transactions log view
  - Add: Multiple checks on PayPal returned variables
  - Change: Enhance CSS compatibility with other styles (thanks Mazeltof)
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
  - Fix: English wording (thanks kasimi)
  - Fix: Unable to use "delete all" in Transactions Log module
  - Remove: Extension version check removed from the Overview module
  - Remove: fsockopen related code
  - Translation: Update Transifex config and Readme files
  - Code improvement

## 1.0.3 - 2017-01-22
  - Add: Add extension events. More information in `/docs/events.md` (Thanks kasimi)
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
  - Change: Add the type of parameter into the method declaration (Thanks ErnadoO)
  - Change: Hide/Show ACP IPN Features using jQuery (Thanks cabot)

## 1.0.1 - 2016-05-09
  - Fix: IN_PHPBB is not defined in `/skouat/ppde/controller/ipn_listener.php`
  - Fix: Error with migration file during installation
  - Add: Convert/purge old data from PayPal Donation MOD 1.0.4

## 1.0.0 - 2016-05-08
  - First release (not published)
