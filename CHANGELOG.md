# Changelog

## 1.0.2
- Fix: Disabling of currency is enhanced by usage of AJAX, but change is not reflected directly
- Fix: Use `is_set()` method in `$request` instead of use `!isset()` on Super Global
- Fix: Donation statistics on index are displayed even if there is no content
- Fix: Missing CSRF check on delete process
- Fix: Remove use of `include_once()`
- Fix: Wrong value on xHTML `<input>` `disabled` attribute
- Change: Add the type of parameter into the method declaration (Thanks ErnadoO)
- Change: Hide/Show ACP IPN Features using jQuery (Thanks cabot)

## 1.0.1 - 2016-05-09

- Fix: IN_PHPBB is not defined in `/skouat/ppde/controller/ipn_listener.php`
- Fix: Error with migration file during installation
- Add: Convert/purge old data from PayPal Donation MOD 1.0.4

## 1.0.0 - 2016-05-08

- First release (not published)
