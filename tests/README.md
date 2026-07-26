# PPDE — Test Suite

Unit and database tests for the PayPal Donation extension.

## Guiding principle

> Test the **deterministic business logic we own**. Leave framework glue,
> network I/O and third-party libraries (PayPal SDK) to functional/integration
> testing.

## Test levels

| Level      | Marker                | What is real                                             | Example                            |
|------------|-----------------------|----------------------------------------------------------|------------------------------------|
| Pure unit  | *(none)*              | Only the tested method; dependencies are mocked          | `actions/core_test.php`            |
| Database   | `@group database`     | Method **+ a real DBMS** (SQLite/MySQL/PostgreSQL/MSSQL) | `migrations/schema_test.php`       |
| Functional | *(not in this suite)* | A full HTTP request on a booted board                    | reserved for `handle()`, ACP flows |

## Running the tests

Run from the **phpBB repository root** (not from `phpBB/`), using PHP 7.2–7.4
(PHPUnit 7.5 is not compatible with PHP 8.x):

```bash
php7.2 phpBB/vendor/bin/phpunit \
  -c phpBB/ext/skouat/ppde/phpunit.xml.dist \
  --bootstrap tests/bootstrap.php
```

Useful filters:

```bash
# Skip database tests (no test_config.php needed)
... --exclude-group database

# A single file / method
... phpBB/ext/skouat/ppde/tests/actions/core_test.php
... --filter test_net_amount
```

> **Database tests** are skipped unless `phpBB/tests/test_config.php` is
> configured. SQLite3 is the simplest choice for local development.

## Coverage map

| Directory      | Covered (unit)                                                                                                           | Left to functional                                 |
|----------------|--------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------|
| `actions`      | net_amount, user_id parsing, sandbox context, `update_raised_amount`, currency, donation_recorder, vars, locale, auth    | `set_guest_acl`, intl wiring                       |
| `api/paypal`   | is_configured, credentials, `test_connection`(missing), `webhook_verify::is_valid` guards, `order_party_extractor` trait | `build()`, cURL calls, successful `openssl_verify` |
| `entity`       | data builder, insert+idempotency, currency, donation_pages bitfield, `main::import`                                      | trivial getters/setters, `generate_text_for_*`     |
| `operators`    | SQL builders (currency, donation_pages, transactions), `build_transaction_url`, state reads                              | `query_*`, `move`/`fix_order`                      |
| `controller`   | ACP helpers, webhook mappers, order soft-descriptor, donor-list sort/URL, display_stats                                  | `handle()`, `display_*`, ACP add/edit/delete       |
| `notification` | core notify (net vs settle), `is_available`, `get_type`/`get_email_template`                                             | `find_users`, `get_title`, `create_insert_array`   |
| `migrations`   | schema + `txn_id` uniqueness                                                                                             | —                                                  |

## Conventions

- One class per file; the class name matches the file name.
- Mock only the dependencies a test actually uses; leave the rest as empty mocks.
- Prefer a **real** `\phpbb\config\config` over an `ArrayAccess` mock.
- Use **Reflection** to reach pure `private`/`protected` methods; guard with `markTestSkipped` if a framework property
  might change.
- Use **anonymous classes + the trait** to test traits in isolation (`transaction_data_builder`,
  `order_party_extractor`).
- Fake SDK objects must declare **real methods** (`safe_call()` relies on `method_exists`).
- Use `@dataProvider` to cover branches without duplicating code.
- Keep comments minimal: explain the **why**, never restate the code.

## Portability rules (learned from CI)

- **PHPUnit 7 ↔ 9:** use a local `set_error_handler` instead of `PHPUnit\Framework\Error\Warning`; stick to
  `assertStringContainsString` (available since 7.5).
- **Idempotency** is tested **behaviourally** (a duplicate insert is rejected), not by index name —
  `sql_unique_index_exists()` is unreliable on PostgreSQL and MSSQL.
- **MySQL strict mode:** always provide `TEXT` columns (e.g. `txn_errors`) in direct INSERTs, otherwise "Field doesn't
  have a default value".
- **ACP tests** calling `generate_link_hash()` must set `session_id` **and** `user_form_salt` on `$GLOBALS['user']`.

## Out of scope (by design)

`event/listener`, `acp/`, `extension_manager`, full HTTP flows (`main_donate`, `webhook_listener::handle`), PayPal SDK
construction and network calls (`build`, `fetch_cert`, create/capture order). These are either trivial wiring or
validated by phpBB functional tests and the ACP "Test connection" button.
