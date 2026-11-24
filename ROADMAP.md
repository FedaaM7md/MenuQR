# Roadmap — MenuQR

Current status: **v0.2.0 identity + demo/security upgrade (release 1.1)**. Demo build,
honest list of what is missing.

## Done in release 1.1

- New identity: charcoal/saffron/olive/cream/tomato, El Messiri + Tajawal,
  printed-menu layout, kitchen board, new logo/favicon/PWA icons.
- Arabic and English copy rewritten; key parity test added.
- Demo mode: one-click admin/cashier/kitchen entry, persistent banner, locked demo
  accounts, `demo:reset`, hourly scheduler, 404 when disabled.
- Security: extra headers, HSTS on HTTPS, honeypot, upload re-encoding to WebP,
  password reset, sign out everywhere, audit log + admin page, tighter demo
  uploads, role/IDOR regression tests.
- Ownership: `LICENSE`, `COPYRIGHT.md`, footer copyright, source headers,
  proprietary package metadata.
- Real screenshots (AR/EN, desktop/mobile) and brand/security docs.
- Fix round: Zaytouna Kitchen demo with 42 Levantine dishes across seven
  categories, allergen notes, about/contact/privacy/terms pages, sample contact
  details, 24/7 demo hours, standard Arabic table terminology, RTL-safe small
  type, mobile card lists and filter sheets, live cart bar, CSV export, footer
  owner credit, and no screenshot/automation dependency.

## Now (before any pilot)

- Cross-browser QA on Safari/iOS and Firefox, and a keyboard/axe accessibility
  audit with recorded results.
- End-to-end browser tests for cart → order → kitchen → served.
- Production database decision (SQLite is fine for one small venue, not for
  multi-tenant scale) and backup automation.
- Real mail provider for password reset; email verification.
- Replace the shared demo dataset with per-visitor demo databases if the demo is
  exposed publicly.

## Next

- Payments (cash/card) and paid/unpaid states; receipt printing (58/80 mm).
- Discount codes, item-level tax categories, delivery fees, tips.
- Inventory counts and automatic “sold out”.
- CSV/PDF exports and a printable daily closing report.
- Signed short-lived customer status links (without the placing session).
- WebSocket/SSE kitchen updates instead of 10-second polling.
- Multi-branch settings/staff; order scheduling; table reservations.
- Audit-log retention/export and alerting.
- PWA install prompt and update-available notification.
- WhatsApp Business API templates (opt-in) instead of the `wa.me` hand-off.

## Later / exploratory

- Multi-tenant SaaS onboarding, custom domains, subscription billing.
- Per-restaurant themes and QR logo customisation.
- Customer accounts, favourites and order history.
- More locales and complete validation catalogues.
- Native wrappers or app-store distribution.

## Known technical debt

- CSP still allows `'unsafe-eval'`/`'unsafe-inline'` because Alpine.js evaluates
  expressions. Removing it needs the Alpine CSP build and refactoring.
- Order numbers use a locked yearly counter; SQLite concurrency should be revisited
  for high volume.
- Cart is session-bound per device/table; switching phones loses the cart.
- Settings are cached for up to an hour but invalidated on save; external DB edits
  need `cache:clear`.
- Arabic validation messages cover the rules used by the app, not the entire
  Laravel catalogue.
- Reports treat every non-cancelled order as revenue (new/preparing included);
  payment state is future work.
- No soft deletes; deleting a menu item keeps order snapshots but loses the link.
- No Lighthouse/PWA score recorded yet.
- Demo seeder password is public by design and must never ship to production.
- Audit log has no retention policy or tamper-evident chain.
