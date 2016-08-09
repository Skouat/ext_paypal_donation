# Changelog

## 1.0.3 - 2016-08-08
Fix: Donors list displays a HTML bullet near of pagination when there is no donors

## 1.0.2 - 2016-08-08
- Fix: Disabling of currency is enhanced by usage of AJAX, but change is not reflected directly
- Fix: Duplicate entries in Transaction Log when status returned by PayPal is different from "completed"
- Fix: Donation statistics on index are displayed even if there is no content
- Fix: IPN listener was unable to works if "Sandbox only for founder" was enabled.
- Fix: Missing CSRF check on delete process
- Fix: Remove use of `include_once()`
- Fix: "Sandbox only for founder" always displayed as enabled even if it was disabled.
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
