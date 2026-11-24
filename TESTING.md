# Testing — MenuQR

Date of final verification: **2026-11-24** (Africa/Cairo)  
Environment: Windows, PHP 8.4.28, SQLite, Node 24, Chrome. Dev port 8104.

Every result below is from a command that was actually run. No tests were
skipped, deleted or weakened.

## 1. Automated tests

```powershell
cd menuqr
php artisan optimize:clear
php artisan test --compact
```

Final output:

```text
{"tool":"phpunit","result":"passed","tests":128,"passed":128,"assertions":577,"duration_ms":6207}
```

Coverage highlights:

- money rounding and totals in piasters; tax/service snapshots;
- option min/max, ownership and client-price rejection;
- order numbering `ORD-YYYY-NNNN` and yearly rollover;
- status transition graph and role limits; served/cancelled final;
- QR token uniqueness, forgery/truncation/uppercase rejection, regeneration;
- public menu search/escaping, inactive tables, closed/not-accepting states;
- cart update/remove/clear and confirmation session binding;
- settings, validation, upload allowlist and WebP re-encoding;
- menu categories/items/options and IDOR protection;
- reports by day/item/category, date range, cancelled exclusion;
- locale default RTL, switch, referrer safety, key parity, Arabic validation;
- PWA manifest, service worker scope/cache rules, offline page;
- demo mode: entry points, one-click roles, banner, locked accounts, reset guard;
- brand naming, attribution, copyright line, about/contact/privacy/terms pages,
  sample contact details, social meta, allergen notes and 24/7 demo hours;
- table terminology, localized Arabic times (ص/م), single demo note, CSV export
  with formula-injection protection and mobile content paths;
- security headers, HSTS on HTTPS, CSRF, honeypot, SQL metacharacters,
  password reset, logout-all, audit log and role access.

## 2. Code style

```powershell
php vendor\bin\pint --test
```

```text
{"tool":"pint","result":"passed"}
```

There is no TypeScript in this stack (Alpine + Blade + plain JS), so no `tsc`
step exists; nothing is claimed about TypeScript checking.

## 3. Production build

```powershell
npm run build
```

Final output (tail):

```text
public/build/assets/el-messiri-latin-500-normal-CHe1spty.woff    18.06 kB
public/build/assets/el-messiri-latin-600-normal-DtTIqwTa.woff    18.12 kB
public/build/assets/app-CU_Sz3vF.css                             81.95 kB │ gzip: 12.05 kB
public/build/assets/app-CLcFUXSi.js                              77.61 kB │ gzip: 25.41 kB
✓ built in 290ms
```

Fonts are emitted as self-hosted WOFF2/WOFF assets; there is no CDN request.

## 4. Dependency audits

```powershell
composer audit
npm audit
```

```text
No security vulnerability advisories found.
found 0 vulnerabilities
```

## 5. Demo reset and scheduler

```powershell
php artisan demo:reset
```

```text
Dropping all tables .. DONE
...
Database\Seeders\DemoSeeder .. DONE
Demo data reset. The shared demo dataset is ready.
```

```powershell
php artisan schedule:list
```

```text
0 * * * * php artisan demo:reset .. Next Due: 9 minutes from now
```

## 6. HTTP smoke test (real server, 127.0.0.1:8104)

| URL | Result |
|---|---|
| `/` | 200, 40,216 bytes |
| `/login` | 200, 16,800 bytes |
| `/forgot-password` | 200, 9,976 bytes |
| `/about`, `/contact`, `/privacy`, `/terms` | 200, 10–11 KB each; restaurant story, sample contact and policies render |
| `/m/{48-char token}` | 200, 343,355 bytes; restaurant name and 42 dishes rendered |
| `/manifest.webmanifest` | 200, 924 bytes |
| `/sw.js` | 200, 3,479 bytes |
| `/offline` | 200, 10,219 bytes |
| `/m/` (no token) | 404 |

The demo sign-in was exercised in Chrome for all three roles (admin, cashier,
kitchen) during screenshot capture. The kitchen feed JSON and role rules are
covered by the automated suite.

## 7. Screenshots (real, no mockups)

Captured with `scripts/screenshots.mjs` against the running app, using Chrome
headless at 1440×1000, 1366×900, 1024×900, 768×1024 and 375×812 @2x. The script
uses the DevTools Protocol and Node's built-in WebSocket; no screenshot library.

```text
home-ar-desktop.png          home-en-desktop.png
home-ar-mobile.png           home-en-mobile.png
login-ar-desktop.png         login-en-desktop.png
login-ar-mobile.png          login-en-mobile.png
menu-ar-mobile.png           menu-en-mobile.png
menu-ar-mobile-cart.png      menu-en-desktop.png
about-ar-desktop.png         contact-ar-mobile.png
dashboard-ar-desktop.png     dashboard-en-desktop.png
dashboard-ar-1024.png        dashboard-ar-mobile.png
dashboard-ar-mobile-menu.png items-ar-desktop.png
items-ar-768.png             items-ar-1366.png
items-ar-mobile.png          orders-ar-mobile.png
tables-ar-desktop.png        reports-ar-desktop.png
activity-ar-desktop.png      settings-ar-desktop.png
kitchen-ar-desktop.png       kitchen-ar-1024.png
kitchen-ar-768.png           kitchen-ar-mobile.png
```

Files are in `docs/screenshots/` and embedded in `README.md`.

## 8. What was not tested

- Real Safari/iOS and Firefox rendering.
- Airplane-mode/offline navigation in a real browser. Manifest, service worker
  and offline page are tested at HTTP/content level only.
- Load, concurrency and high-volume order numbering.
- Penetration testing or a formal accessibility audit with axe/Lighthouse.
- Email delivery through a real SMTP provider (the demo uses the log mailer).
- Kitchen polling across two live browsers (the feed and JSON shape are tested).

These are recorded as limitations in `ROADMAP.md` and
`docs/SECURITY-REVIEW.md`; they are not claimed as passing.
