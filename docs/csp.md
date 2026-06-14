# Content Security Policy (CSP) Configuration

Since version 3.1.0, PayPal Donation Extension (PPDE) uses the **PayPal REST API**
and the **PayPal JavaScript SDK** to display the donation buttons and process
payments. If your board enforces a [Content Security Policy](https://developer.mozilla.org/docs/Web/HTTP/CSP),
you must allow PayPal's domains, otherwise the donation buttons will not appear
and payments will fail.

> **Note:** phpBB does **not** send a CSP header by default. You only need this
> guide if a CSP is set by your web server (Apache/Nginx), a reverse proxy/CDN
> (e.g. Cloudflare), or a security hardening extension. If you have never
> configured a CSP, donations will work out of the box and you can ignore this
> document.

---

## Required directives

Allow the following PayPal domains:

* `*.paypal.com`
* `*.paypalobjects.com`
* `*.venmo.com`

| Directive                 | Why PPDE needs it                                                        |
|---------------------------|--------------------------------------------------------------------------|
| `script-src`              | Loads the PayPal JS SDK (`https://www.paypal.com/sdk/js`).               |
| `connect-src`             | The SDK performs background requests to PayPal.                          |
| `frame-src` / `child-src` | The donation buttons and the payment window are rendered inside iframes. |
| `img-src`                 | PayPal button graphics and tracking pixels (plus `data:` images).        |
| `style-src`               | The SDK injects inline styles for its buttons.                           |

> **Sandbox:** the wildcard `*.paypal.com` already covers
> `www.sandbox.paypal.com`. No extra entry is required for sandbox testing.

---

## Recommended policy

PPDE loads all of its own JavaScript from external files (no inline `<script>`,
no inline event handlers). As a result, you do **not** need `'unsafe-inline'` in
`script-src` — your own scripts are covered by `'self'`.

```
default-src 'self';
script-src  'self' https://*.paypal.com https://*.paypalobjects.com https://*.venmo.com;
connect-src 'self' https://*.paypal.com https://*.paypalobjects.com https://*.venmo.com;
frame-src   'self' https://*.paypal.com https://*.paypalobjects.com https://*.venmo.com;
child-src   'self' https://*.paypal.com https://*.paypalobjects.com https://*.venmo.com;
img-src     'self' https://*.paypal.com https://*.paypalobjects.com https://*.venmo.com data:;
style-src   'self' https://*.paypal.com https://*.paypalobjects.com https://*.venmo.com 'unsafe-inline';
```

> `style-src` still needs `'unsafe-inline'` (or a nonce) because the PayPal SDK
> injects inline styles for its buttons. This requirement comes from PayPal, not
> from PPDE.

---

## Server examples

### Apache (`.htaccess` or virtual host)

```apache
Header set Content-Security-Policy "default-src 'self'; script-src 'self' https://*.paypal.com https://*.paypalobjects.com https://*.venmo.com; connect-src 'self' https://*.paypal.com https://*.paypalobjects.com https://*.venmo.com; frame-src 'self' https://*.paypal.com https://*.paypalobjects.com https://*.venmo.com; child-src 'self' https://*.paypal.com https://*.paypalobjects.com https://*.venmo.com; img-src 'self' https://*.paypal.com https://*.paypalobjects.com https://*.venmo.com data:; style-src 'self' https://*.paypal.com https://*.paypalobjects.com https://*.venmo.com 'unsafe-inline';"
```

### Nginx

```nginx
add_header Content-Security-Policy "default-src 'self'; script-src 'self' https://*.paypal.com https://*.paypalobjects.com https://*.venmo.com; connect-src 'self' https://*.paypal.com https://*.paypalobjects.com https://*.venmo.com; frame-src 'self' https://*.paypal.com https://*.paypalobjects.com https://*.venmo.com; child-src 'self' https://*.paypal.com https://*.paypalobjects.com https://*.venmo.com; img-src 'self' https://*.paypal.com https://*.paypalobjects.com https://*.venmo.com data:; style-src 'self' https://*.paypal.com https://*.paypalobjects.com https://*.venmo.com 'unsafe-inline';" always;
```

> Adapt the directives above to merge them with any existing CSP on your board.
> Only the PayPal-specific entries are mandatory for PPDE.

---

## Strict policy with a nonce (advanced)

If you already run a strict, nonce-based CSP, add PayPal's domains alongside your
nonce and use `'strict-dynamic'` so the trust is propagated to the SDK that PPDE
loads dynamically:

```
script-src 'self' 'nonce-YOUR_NONCE' 'strict-dynamic' https://*.paypal.com https://*.paypalobjects.com https://*.venmo.com;
```

---

## The webhook is NOT affected by CSP

CSP only applies to the **browser**. The PPDE webhook listener
(`app.php/donate/webhook`) receives **server-to-server** requests directly from
PayPal, so CSP has no effect on it.

If donations succeed in the browser but are not recorded on your board, the CSP
is not the cause. Instead, check that the webhook URL is reachable and not
blocked by other protections such as a Web Application Firewall (WAF), anti-bot
rules, or HTTP basic authentication.

---

## Troubleshooting

### The donation buttons do not appear

* Open your browser console (F12).
* Look for an error such as:
  *“Refused to load the script 'https://www.paypal.com/sdk/js…' because it
  violates the following Content Security Policy directive: script-src …”*
* This means `script-src` is missing the PayPal domains. Add them as shown above.

### The buttons appear but the payment window is blank / does not open

* Check the console for a `frame-src` (or `child-src`) violation.
* Add the PayPal domains to `frame-src` **and** `child-src`.

### Buttons look unstyled

* Check the console for a `style-src` violation.
* Add `'unsafe-inline'` (or a nonce) and the PayPal domains to `style-src`.

### Background requests fail

* Check the console for a `connect-src` violation.
* Add the PayPal domains to `connect-src`.
* Note: PPDE's own AJAX calls (create/capture order) target **your** board and
  are covered by `'self'`.

### Still not working?

* Confirm the CSP is actually the source by temporarily switching the header to
  report-only mode:
  `Content-Security-Policy-Report-Only: …`
  The buttons will load while violations are still reported in the console.
* Report persistent issues in the
  [PayPal Donation topic at phpBB.com](https://www.phpbb.com/community/viewtopic.php?f=456&t=2358616)
  or open an issue in the
  [Issue Tracker](https://github.com/Skouat/ext_paypal_donation/issues).
