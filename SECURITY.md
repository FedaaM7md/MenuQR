# Security — MenuQR

> **Demo build, not production-ready.** It ships with public demo credentials and
> a shared dataset. Read `docs/SECURITY-REVIEW.md` for the full review.

Copyright (c) 2026 Fedaa Mohamed. All rights reserved.

## Implemented controls

| Area | Implementation |
|---|---|
| CSRF | Laravel web middleware; all state-changing forms include `@csrf`; Alpine kitchen updates submit `FormData` with the token. |
| Authentication | Session login, bcrypt (12 rounds), session regeneration on success, invalidation + token regeneration on logout, “sign out of every device”. |
| Login throttling | 5 failed attempts per email+IP per minute (`LoginRequest`) plus route throttle (20/min) and a 10/min demo-login throttle. |
| Password policy | Staff passwords: min 8 with letters and numbers. Reset: min 10 with letters and numbers, hashed token, expiry. |
| Password reset | `/forgot-password` + `/reset-password/{token}`, log mailer in the demo, generic responses, rate-limited, blocked for demo accounts. |
| Inactive accounts | `EnsureRole` rejects inactive accounts on every protected route; login logs them out. |
| Authorization | Admin/Cashier/Kitchen roles, `role` middleware, Gates, `OrderPolicy`, and explicit `$this->authorize()` calls. Cross-role and IDOR tests. |
| Server-side validation | Form Requests for all writes; prices, options, tax, totals, table identity and order status are recomputed/ignored from client input. |
| Uploads | `image` + allowlisted MIME, dynamic size limit (2 MB staff / 512 KB demo), dimension guard, GD re-encode to WebP, random filename, metadata stripped. SVG rejected. |
| Escaping | Blade auto-escaping; no `{!! !!}` with user data; Alpine `x-text` for kitchen JSON; activity log output escaped. |
| Table tokens | 48-character random bearer tokens, unique, regenerable; invalid/truncated/uppercase tokens 404. Orders bind the table from the URL. |
| Order integrity | Explicit transition map, role-specific transitions, served/cancelled final, session-bound confirmation pages. |
| Public abuse | Honeypot on checkout; IP throttles on menu, cart, order, confirmation, locale, login, demo and password reset. |
| Security headers | CSP (self, no third-party), HSTS on HTTPS, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, COOP/CORP, `X-Permitted-Cross-Domain-Policies`. |
| Sessions | HttpOnly, SameSite=Lax, Secure when HTTPS, database driver, 120-minute lifetime, encrypted payload option documented. |
| Audit | `activity_logs` for login, logout-all, password reset, demo sign-in/reset, settings, staff, menu, tables and order status. Admin-only activity page. |
| Demo safety | Demo accounts locked (identity/role/password), cannot create admins, cannot be deleted; hourly reset; `DEMO_MODE=false` removes entry points and routes 404. |
| Secrets | `.env` ignored; `.env.example` dummy values and `APP_DEBUG=false`; no keys or real phone numbers committed. |
| Dependencies | MIT/BSD/Apache/ISC/OFL only; `composer audit` and `npm audit` clean at review time. |
| PWA scope | Service worker never caches staff/admin pages, order confirmations or non-GET requests. |

## Known limitations

- **Public demo credentials** (`Demo1234!`) and a shared dataset. Anyone can
  change demo data until the next reset. Never put real data in the demo.
- **CSP allows `'unsafe-inline'`** for two tiny UI actions (print and reload).
  Alpine runs the official CSP build, so `'unsafe-eval'` is removed; output is
  escaped and no external script is loaded.
- No 2FA, SSO, email verification or device management beyond “sign out all”.
- Password reset email is written to the log (`MAIL_MAILER=log`) in the demo.
- No WAF, DDoS protection, malware scanning or backup automation.
- SQLite lacks managed-database concurrency and operational tooling.
- Table tokens are bearer secrets in URLs; no expiry, only regeneration.
- Uploaded images are re-encoded locally; production should add object storage
  and a virus scan if untrusted users upload files.
- Order and customer data are stored unencrypted at rest. No payment data is
  handled because there is no payment integration.
- Audit log has no retention policy or tamper-evident chain.
- Trusted proxies and host allow-lists are not configured for a real domain.
- No penetration test has been performed.

## Production checklist

1. `APP_ENV=production`, `APP_DEBUG=false`, fresh `APP_KEY`, MySQL/PostgreSQL and
   automated backups.
2. Disable demo mode, delete demo users/data, create real admin accounts.
3. HTTPS + HSTS + secure/HttpOnly/SameSite cookies, trusted proxies, real domain.
4. Replace the permissive CSP with nonces or a CSP-safe Alpine build.
5. Use a real mail provider for password resets; add email verification/2FA.
6. Add object storage for uploads, retention for the audit log, monitoring and
   alerting.
7. Penetration test and browser E2E/accessibility tests before launch.

## Reporting

This demo has no production security contact. For the commercial product, route
reports through Fedaa’s private security channel; do not publish exploit details.
