# Security Review — MenuQR (release 1.1)

Date: 2026-11-24  
Scope: `menuqr` (demo build, SQLite, single restaurant)  
Method: code review, automated tests, real HTTP smoke test on `127.0.0.1:8104`,
`composer audit`, `npm audit`.

This is a demo build. It is not a production deployment and has not been
penetration-tested. Read `SECURITY.md` for the honest limitations.

## 1. Threat model

| Asset | Threat | Control |
|---|---|---|
| Staff accounts and roles | credential stuffing, role escalation | login throttling (5/min per email+IP), password policy, session regeneration, role middleware + gates, inactive-account block |
| Restaurant data | unauthorised edit via admin routes | `role:admin` middleware + `$this->authorize()` on every admin action |
| Customer orders | price tampering, fake orders, spam | server-side re-pricing in minor units, option ownership checks, CSRF, honeypot, IP throttles |
| Table QR links | guessing, sharing after print | 48-char random token, regeneration invalidates old links, inactive tables 404 |
| Uploaded images | malicious file, SVG/HTML upload, metadata leak | content-type + `image` validation, allowlist (JPG/PNG/WebP), size limits, GD re-encode to WebP, random filename, metadata stripped |
| Demo dataset | destructive visitors, data pollution | shared dataset, hourly reset, demo accounts locked, demo restrictions, 404 when disabled |
| Session | fixation, theft, cross-site use | regenerate on login, HttpOnly, SameSite=Lax, Secure in HTTPS envs, logout everywhere |
| Passwords | offline cracking, reset abuse | bcrypt 12, reset token hashed in DB with expiry, rate-limited reset request, generic responses |
| Activity | repudiation | audit log for settings, staff, menu, tables, order status, login and demo events |
| Headers | clickjacking, MIME sniffing, referrer leak | CSP, X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy, COOP/CORP, HSTS on HTTPS |

## 2. What was checked and fixed

### Headers

- Added `Cross-Origin-Opener-Policy: same-origin`,
  `Cross-Origin-Resource-Policy: same-origin`, `X-DNS-Prefetch-Control: off`.
- Added `Strict-Transport-Security: max-age=31536000; includeSubDomains` **only**
  when the request is HTTPS.
- CSP now also blocks `object-src`, limits media/worker/manifest to self, and
  keeps `frame-ancestors 'self'`.
- The CSP build of Alpine is used, so `'unsafe-eval'` is no longer present.
  `'unsafe-inline'` remains for two tiny inline UI actions (print/reload); output
  is escaped and no third-party script is loaded.

### Authentication

- Login throttling remains 5 failures per email+IP, then 60 seconds.
- Session ID is regenerated on success; locale is stored after regeneration.
- Inactive accounts are logged out and refused.
- Password reset added: `/forgot-password` and `/reset-password/{token}` using
  Laravel’s broker, log mailer, hashed token, expiry and a 10-character
  letter+number policy. Responses are generic to prevent account enumeration.
- Demo accounts cannot request or complete a reset.
- “Sign out of all devices” deletes the user’s rows from the sessions table.
- Demo login is throttled to 10 requests/minute per IP.

### Authorization

- All admin routes are behind `auth` + `role:admin`.
- Controller actions call gates/policies (`manage-settings`, `manage-menu`,
  `manage-tables`, `manage-staff`, `view-reports`, `view-orders`, `view-kitchen`).
- `StaffController@store/update` now calls `authorize('manage-staff')`
  explicitly in addition to the route middleware.
- Order status transitions check both the workflow and the role; IDOR tests
  cover option groups belonging to another item, confirmation pages opened with
  another table token, and cross-role access.

### Input/output

- Every write uses a FormRequest or explicit `$request->validate()`.
- Mass assignment is limited by `$fillable` on models.
- Blade output is escaped; no `{!! !!}` with user data. The only unescaped
  strings are internal SVG path data in the icon component.
- Public search is parameterised and capped at 60 characters; SQL-metacharacter
  test passes.
- Public order payload cannot set status, totals, table or creator.
- Reports can be exported as CSV. Text cells starting with `=`, `+`, `-`, `@`,
  tab or carriage return are prefixed with an apostrophe to prevent spreadsheet
  formula injection; the response is `text/csv` with `no-store`.

### Uploads

- `image` + `mimes:jpg,jpeg,png,webp` and dynamic size limits (2 MB staff,
  512 KB demo).
- New `App\Services\ImageUploader` decodes the image, rejects invalid dimensions
  (max 8000 px / 40 MP), resizes to 1600 px, re-encodes as WebP and stores a
  random 40-char filename on the public disk. EXIF and other metadata are gone.
- SVG is rejected. Stored files are served as `image/webp` by the web server.

### Public forms

- Honeypot `website` field on checkout; `prohibited` rule rejects bots.
- Throttles: menu 120/min, cart add 40/min, cart mutations 60/min, order 12/min,
  confirmation 60/min, locale 30/min, login 20/min route + 5 failures key,
  demo 10/min, reset request 5/min, reset submit 6/min.
- CSRF is enabled globally; tests assert the token is present.

### Secrets and configuration

- `.env` is git-ignored; `.env.example` has dummy values and `APP_DEBUG=false`.
- `APP_NAME=MenuQR`; `APP_KEY` is not committed.
- Errors are not rendered with debug in the example environment.
- No secrets are written to the audit log; only action, subject, IP and a short
  description are stored.

### Dependencies

- `composer audit`: no advisories at review time.
- `npm audit`: 0 vulnerabilities at review time.
- All direct dependencies are MIT/BSD/Apache-2.0/OFL. No GPL/AGPL/LGPL.

## 3. Demo-specific risks

The demo is intentionally shared. Anyone can sign in as admin, cashier or
kitchen and change the menu, settings and orders. That is the point of the demo,
but it means:

- do not put real customer data in the demo database;
- the hourly `demo:reset` wipes and reseeds the database;
- demo accounts cannot change email/password/role, delete themselves, or create
  another admin;
- uploads are capped at 512 KB for demo users;
- when `DEMO_MODE=false`, the demo routes return 404 and the entry points vanish.

Residual risk: a visitor can still deface the shared demo during the hour, and
other visitors will see it until the reset. Per-visitor demo databases are on the
roadmap.

## 4. Remaining risks / not production-ready

1. **CSP still allows inline UI handlers.** Mitigation: no third-party scripts,
   escaped output, and the Alpine CSP build without `'unsafe-eval'`.
2. **SQLite** is used for the demo; there is no database-level row security,
   replication or backup routine.
3. **No WAF / bot protection** beyond throttles and a honeypot.
4. **No 2FA, no SSO, no device management** beyond “sign out everywhere”.
5. **Password reset mail is written to the log** (`MAIL_MAILER=log`); production
   needs a real mail provider and a deliverability plan.
6. **Audit log has no retention policy or tamper-evident chain.**
7. **No payment processing**, therefore no PCI scope, but also no receipt or
   refund controls.
8. **Shared demo dataset** as described above.
9. **No automated dependency update bot**; audits are point-in-time.
10. **No penetration test.** The automated tests are regression tests, not proof
    of security.

## 5. Verification commands

```powershell
php artisan test
php vendor\bin\pint --test
php composer.phar audit   # via composer audit
npm audit
npm run build
```

See `TESTING.md` for the recorded output.
