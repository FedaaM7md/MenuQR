# Architecture — MenuQR (release 1.1)

Copyright (c) 2026 Fedaa Mohamed. All rights reserved.

## 1. Shape

MenuQR is a server-rendered Laravel application (Blade + Alpine + Tailwind,
SQLite) for one restaurant. There is no SPA, no external API and no third-party
script at runtime.

```
Browser
  │  HTML + self-hosted assets
  ▼
Laravel 13 (routes/web.php)
  │  middleware: SecurityHeaders, SetLocale, auth, role, throttle
  ▼
Controllers ── FormRequests ── Services ── Eloquent models ── SQLite
  │                              │
  │                              ├─ CartService (session cart)
  │                              ├─ PricingService (minor units)
  │                              ├─ OrderService / OrderWorkflow
  │                              ├─ ReportService
  │                              ├─ QrCodeService (endroid, local SVG)
  │                              ├─ ImageUploader (GD → WebP)
  │                              └─ Audit (activity_logs)
  ▼
Blade views (public menu, auth, admin, staff, kitchen, print)
```

## 2. Domain model

- `Setting` — one row, cached for an hour, invalidated on save. Restaurant
  identity, EGP, tax/service in basis points, hours, WhatsApp, order switch.
- `Category` → `MenuItem` → `OptionGroup` → `MenuOption`.
- `RestaurantTable` — 48-char token; the token is the public credential.
- `Order` → `OrderItem` → `OrderItemOption` — snapshots of names, prices and
  choices so later menu edits do not rewrite history.
- `OrderSequence` — per-year counter for `ORD-YYYY-NNNN`.
- `User` — role (`admin`, `cashier`, `kitchen`), active flag, locale, demo flag.
- `ActivityLog` — audit trail with user, action, subject, IP, user agent, meta.

Money is always an integer in piasters. Percentages are basis points. `Money`
formats for display only.

## 3. Request flow

### Public menu

`GET /m/{token}` → `PublicMenuController@show` → active table check → active
categories/items with options → `menu.show`. The QR token is the only credential;
regeneration invalidates printed links. The menu recalculates the cart total for
the sticky bar, and the category strip uses an IntersectionObserver for the active
chip. Allergen notes come from the item and are repeated in the menu footer.

### Public restaurant pages

`/about`, `/contact`, `/privacy` and `/terms` render the fictional restaurant
story, sample contact details, privacy policy and order terms. They share the
public shell and its Open Graph metadata.

### Order

`POST /m/{token}/orders` → `PublicOrderRequest` (type, optional customer,
honeypot) → `OrderService@place` inside a transaction:
reload each item from DB, re-price options, validate min/max and ownership,
compute tax/service, allocate the yearly number, snapshot lines and choices,
clear the cart. The confirmation page requires the placing session and the same
table token.

### Kitchen

`GET /staff/kitchen` renders the board. Alpine polls
`GET /staff/kitchen/feed` every 10 seconds, and on tab focus, with
`Cache-Control: no-store`. Status actions submit `PATCH /staff/orders/{id}/status`
with CSRF; `OrderWorkflow` checks the transition and the role.

### Admin

All `/admin/*` routes use `auth` + `role:admin`; controllers also call gates.
Settings/menu/table/staff writes are validated by FormRequests and logged.

### Demo

`POST /demo-login` (when `DEMO_MODE=true`) signs in a demo account by role,
regenerates the session, stores locale and redirects to the role home. Demo
accounts are locked in `StaffController`; uploads use the smaller limit.
`demo:reset` rebuilds the schema and reseeds; the scheduler runs it hourly.

## 4. Front end

- `resources/css/app.css` holds the Tailwind theme (charcoal/saffron/olive/cream/
  tomato), the component layer (paper, ticket, menu leader, buttons, tables,
  kitchen board), print rules and the logical sidebar transform.
- Fonts are imported from `@fontsource` and bundled by Vite; only self-hosted
  WOFF2/WOFF files are served.
- `resources/js/app.js` contains the CSP-safe Alpine components: shell drawer,
  item dialog, kitchen board, menu navigation, search and small page helpers, plus
  service-worker registration.
- Blade components: logo, icon (original SVG set), money, order totals, status
  badge, language switch, menu item row/dialog.
- RTL/LTR uses logical properties and `dir` from the locale; no separate CSS.

## 5. PWA

`PwaController` serves `resources/pwa/manifest.webmanifest` and `sw.js` from the
root scope. The service worker caches the menu shell and static assets, never
staff/admin pages, confirmations or non-GET requests. Orders are disabled in the
UI when offline.

## 6. Security layers

1. Response headers (`SecurityHeaders`).
2. Session and CSRF (Laravel web middleware).
3. Authentication + throttling (`LoginRequest`).
4. Authorization (role middleware, gates, `OrderPolicy`).
5. Validation and server-side pricing.
6. Upload decoding/re-encoding (`ImageUploader`).
7. Audit trail for sensitive actions.
8. Demo isolation and reset.

## 7. Deployment shape

Single PHP process + SQLite for the demo; `artisan serve` on port 8104 locally.
Production would use PHP-FPM/Nginx, MySQL/PostgreSQL, a queue worker, the
scheduler and object storage. See `ROADMAP.md` and `docs/SECURITY-REVIEW.md`.

## 8. Tests

`tests/Feature` covers auth/roles, public menu, cart/order pricing, status
workflow, QR tokens, settings/uploads, reports, PWA endpoints, locale parity,
demo mode, brand/naming, security headers, honeypot, reset flow, audit log and
logout-all. `tests/Unit` covers money rounding. Total: see `TESTING.md`.
