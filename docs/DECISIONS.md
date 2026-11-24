# Decisions — MenuQR (release 1.1)

Copyright (c) 2026 Fedaa Mohamed. All rights reserved.

## D1. One restaurant per install, not multi-tenant

MenuQR serves one restaurant, with one `Setting` row. Multi-branch and SaaS
onboarding are future work. This keeps the demo honest and the data model small.

## D2. Money in integer piasters, percentages in basis points

Every amount is an integer in minor units; `1400` means 14%. All totals are
recomputed on the server. The client never supplies a price, total, status or
table identity that is trusted.

## D3. Tax and service are independent, not compounded

Both apply to the subtotal and are snapshotted on the order. This is simple to
explain to a restaurant and matches the demo copy. A future invoicing mode may
need compounding/line-level tax.

## D4. The table token is the public credential

No customer account. A 48-character random token is the only thing needed to
open a menu. Regeneration invalidates printed codes. Tokens have no expiry, which
is a documented risk; expiry would need a reprint workflow.

## D5. Cart is session-bound

The cart lives in the session per browser/device. It is deliberately not a
database cart: guests have no identity, and clearing the session should clear the
cart. Switching phones loses the cart (documented).

## D6. Order snapshots, not live menu references, for display

`OrderItem` stores the item name, unit price and option names/deltas at order
time. Editing the menu later never rewrites history. The `menu_item_id` remains
for reporting joins.

## D7. Workflow is explicit and role-limited

`Order::TRANSITIONS` defines the legal graph; `OrderWorkflow` applies the role
rules. Served/cancelled are final. Kitchen can move new → preparing → ready but
cannot serve or cancel. Cashier/admin can do the full path.

## D8. Server-rendered Blade, Alpine only for local interactions

No SPA and no client router. Alpine handles the item dialog, kitchen polling,
copy link and network state. This keeps the public menu fast and the security
surface small. The project now uses Alpine's official CSP build, with all
expressions in registered components and no `unsafe-eval` in the CSP.

## D9. Printed-menu visual language, not a SaaS dashboard

release 1.1 replaced the indigo/teal identity with charcoal, saffron, olive, cream and
tomato; El Messiri + Tajawal; dotted leaders; ticket cuts; a dark kitchen board.
The customer menu is a single printed column, not a card grid. Rationale: the
task is a restaurant menu, and the interface should feel like one.

## D10. Western digits with tabular spacing

Egypt commonly uses Western digits in digital menus. We use Western digits with
`font-variant-numeric: tabular-nums` and Arabic currency labels («ج.م») rather than
Arabic-Indic digits. This keeps prices aligned and avoids font fallback gaps.

## D11. Demo mode is explicit and removable

`DEMO_MODE` defaults true locally. It controls the landing/login entry points,
the demo bar, the locked demo accounts and the hourly reset. When false, the
routes return 404. The demo dataset is shared, not per visitor; per-visitor
isolation is roadmap work and documented as a risk.

## D12. Demo accounts are locked, not merely discouraged

Server-side rules in `StaffController` keep demo email, password, role and active
flag fixed, block creating admins, and block deleting demo accounts. Hiding fields
in the UI alone would be insufficient.

## D13. Uploads are always re-encoded

We do not trust MIME headers or extensions. `ImageUploader` decodes with GD,
enforces dimensions, resizes to 1600 px, writes WebP with a random name and drops
metadata. SVG is rejected. The original filename and EXIF never reach disk.

## D14. Password reset is real, mail is local

The reset flow uses Laravel’s broker with hashed tokens and expiry. In the demo
`MAIL_MAILER=log`; production needs a real provider. Reset responses are generic
to avoid account enumeration; demo accounts are never resettable.

## D15. Audit log is append-only by convention

`activity_logs` records sensitive actions with user, subject, IP and a short
description. There is no retention or tamper-evident chain yet; both are roadmap
items. We never log passwords, tokens or full request payloads.

## D16. Settings cache stores attributes only

`Setting::current()` caches the attribute array (not the Eloquent model) to avoid
`__PHP_Incomplete_Class` errors. Saves and deletes invalidate the cache. External
DB edits need `cache:clear`.

## D17. SQLite is the required demo database

It keeps the demo portable and zero-config. It is not a production concurrency
choice; the roadmap names MySQL/PostgreSQL and backups.

## D18. Compiled assets are committed

`public/build` is committed so the demo renders without running Node. This is a
deliberate deviation from Laravel’s default gitignore; rebuild with `npm run
build` after changing CSS/JS/Blade.

## D19. Screenshots are real

The screenshots in `docs/screenshots/` were captured by `scripts/screenshots.mjs`
against the running app using the installed Chrome. No mockups, no stock images.
The README does not claim screenshots that do not exist.

## D20. Tests before claims

Every feature claim in the report is backed by a test or a real command run. The
suite includes Arabic/English key parity, brand naming, demo restrictions,
headers, uploads, audit, password reset and logout-all. A failing test is never
deleted or weakened to make the build green.

## D21. Standard Arabic for table terms

The UI uses طاولة / طاولات, not the Egyptian colloquial ترابيزة. It reads clearly
in menu titles, order details and the kitchen screen, and avoids mixing dialect
with the rest of the Modern Standard Arabic copy.

## D22. A believable fictional restaurant

The public side presents «زيتونة — مطبخ شامي» / Zaytouna Kitchen in Nasr City: a
42-item Levantine menu across seven categories, allergen notes, about/contact/
privacy/terms pages and sample contact details. All names, prices and numbers are
fictional and labelled as a demo in the footer and in demo sessions.

## D23. Own CSV export with formula protection

Reports export through a streamed CSV built in the controller. Text cells are
prefixed when they start with a spreadsheet formula character, so a malicious item
name cannot execute in Excel. No export library is added.

## D24. No screenshot library

Screenshots are captured with a small Node script that talks to Chrome through the
DevTools Protocol using Node's built-in WebSocket. This keeps the dependency list
to the framework base and avoids a browser-automation package.

## D25. Alpine CSP build

The project uses `@alpinejs/csp`, the official MIT CSP build from the same Alpine
project. All reactive expressions live in registered `Alpine.data` components;
`unsafe-eval` is removed from the CSP. `unsafe-inline` remains only for two tiny
UI actions (print and reload) bundled in Blade.
