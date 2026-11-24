# Third-Party Notices — MenuQR

This project uses only permissive licences: **MIT, BSD-2-Clause, BSD-3-Clause,
ISC, Apache-2.0** and the **SIL Open Font License 1.1** for fonts. No GPL, AGPL or
LGPL code is included. Full texts live in `vendor/<package>/LICENSE`,
`node_modules/<package>/LICENSE` and `resources/fonts/OFL-*.txt`.

Copyright (c) 2026 Fedaa Mohamed applies to the original MenuQR code, design,
logo, copy and documentation only. Third-party work remains with its authors.

## Direct Composer dependencies

| Package | Version | Licence | Purpose |
|---|---|---|---|
| laravel/framework | 13.43.0 | MIT | Application framework |
| endroid/qr-code | 6.1.3 | MIT | Local SVG QR generation |
| laravel/tinker | 3.2.0 | MIT | Local REPL (dev) |
| laravel/pail | 1.3.0 | MIT | Local log viewer (dev) |
| laravel/pao | 1.1.6 | MIT | Test output formatting (dev) |
| laravel/pint | 1.32.3 | MIT | Code style (dev) |
| phpunit/phpunit | 12.5.45 | BSD-3-Clause | Test runner (dev) |
| fakerphp/faker | 1.24.1 | MIT | Test/demo data (dev) |
| mockery/mockery | 1.6.15 | BSD-3-Clause | Test doubles (dev) |
| nunomaduro/collision | 8.9.6 | MIT | Console errors (dev) |

## Direct npm dependencies

| Package | Version | Licence | Purpose |
|---|---|---|---|
| @alpinejs/csp | 3.17.5 | MIT | CSP-safe Alpine build for dialogs, kitchen polling and navigation |
| tailwindcss | 4.3.5 | MIT | CSS framework |
| @tailwindcss/vite | 4.3.5 | MIT | Tailwind + Vite |
| vite | 8.3.8 | MIT | Asset bundler |
| laravel-vite-plugin | 3.2.1 | MIT | Laravel + Vite |
| @fontsource/el-messiri | 5.3.0 | OFL-1.1 | Display font files (build-time package; fonts self-hosted) |
| @fontsource/tajawal | 5.3.0 | OFL-1.1 | Body font files (build-time package; fonts self-hosted) |

## Fonts

| Font | Files | Licence |
|---|---|---|
| El Messiri | `node_modules/@fontsource/el-messiri/files/*` bundled by Vite | SIL OFL 1.1 — `resources/fonts/OFL-El-Messiri.txt` |
| Tajawal | `node_modules/@fontsource/tajawal/files/*` bundled by Vite | SIL OFL 1.1 — `resources/fonts/OFL-Tajawal.txt` |

Both fonts are self-hosted and bundled into `public/build`; there is no Google
Fonts or CDN request at runtime.

## Notable transitive packages

| Package | Licence |
|---|---|
| bacon/bacon-qr-code | BSD-2-Clause |
| symfony/* (console, http-foundation, http-kernel, routing, translation, …) | MIT |
| nesbot/carbon | MIT |
| guzzlehttp/guzzle | MIT |
| vlucas/phpdotenv | BSD-3-Clause |
| tijsverkoyen/css-to-inline-styles | BSD-3-Clause |
| voku/portable-ascii | MIT |
| sebastian/* | BSD-3-Clause |
| rollup / rolldown native bindings | MIT |
| @vue/reactivity (Alpine dependency) | MIT |
| postcss, lightningcss, nanoid, picocolors and other Vite/Tailwind transitives | MIT / ISC / Apache-2.0 |

Exact resolution is pinned in `composer.lock` and `package-lock.json`.

## Original work

- The **MenuQR** name, wordmark, cloche/QR logo, favicon, PWA icons, colour
  system, layouts, illustrations and all application code are original to this
  project.
- Icons are original SVGs drawn for this project.
- No third-party logos, brand marks, CDN scripts, trackers or analytics are
  bundled.
- Demo restaurant names, staff names, menu items and orders are fictional and
  clearly labelled demo content.

## Audits at review time

- `composer audit`: no security vulnerability advisories found.
- `npm audit`: 0 vulnerabilities.

These are point-in-time results; re-run before deployment.
