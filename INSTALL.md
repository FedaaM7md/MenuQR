# Installation — MenuQR

Commands below are the ones actually used on this machine (Windows, PowerShell,
PHP 8.4, Node 24). No Docker, WSL or global installs.

## 1. Requirements

| Tool | Path / version |
|---|---|
| PHP | `php` (8.4.28, `gd`, `fileinfo`, `intl`, `mbstring`, `pdo_sqlite`) |
| Composer | `composer` (2.11.2) |
| Node.js | 24.x |
| npm | 11.x |
| Database | SQLite (`database/database.sqlite`) |
| Dev port | **8104** |

## 2. Install dependencies

```powershell
cd menuqr
composer install
npm install
```

`npm install` brings the self-hosted OFL fonts (`@fontsource/el-messiri`,
`@fontsource/tajawal`). No runtime CDN is used. Screenshots use the installed
Chrome through a small Node script with no extra package.

## 3. Environment

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

`.env.example` is safe to commit: dummy values, `APP_DEBUG=false`, demo mode on.
Adjust locally if needed:

```dotenv
APP_NAME="MenuQR"
APP_URL=http://127.0.0.1:8104
APP_LOCALE=ar
APP_FALLBACK_LOCALE=en
DB_CONNECTION=sqlite
SESSION_DRIVER=database
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
SESSION_SECURE_COOKIE=false
DEMO_MODE=true
DEMO_RESET_ENABLED=true
```

Set `SESSION_SECURE_COOKIE=true` and `APP_URL=https://…` behind HTTPS.
Never commit `.env`.

## 4. Database and demo data

```powershell
php artisan migrate:fresh --seed --force
php artisan storage:link
```

`DemoSeeder` is clearly labelled demo data: the fictional restaurant «زيتونة —
مطبخ شامي», three demo staff accounts, 42 Levantine dishes in seven categories,
six tables and 36 demo orders across recent weeks. Prices, phone numbers and emails are sample data.

Demo accounts:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@demo.menuqr.test` | `Demo1234!` |
| Cashier | `cashier@demo.menuqr.test` | `Demo1234!` |
| Kitchen | `kitchen@demo.menuqr.test` | `Demo1234!` |

The normal entry point is the **Try the demo / جرّب النسخة التجريبية** button; it
signs in without typing. These credentials are public on purpose and exist only
for the demo.

## 5. Build assets

```powershell
npm run build
```

Compiled assets are committed under `public/build` so the demo renders without a
Node toolchain. Rebuild after any CSS/JS/Blade change.

## 6. Run locally

```powershell
php artisan serve --host=127.0.0.1 --port=8104
```

Open `http://127.0.0.1:8104`. Stop the server with `Ctrl+C` when done; port 8104
is the only port used by this project.

## 7. Demo reset and scheduler

Manual reset (wipes and reseeds the demo database):

```powershell
php artisan demo:reset
```

Automatic reset every hour when demo mode is enabled:

```powershell
php artisan schedule:work
```

In production use a cron entry instead:

```cron
* * * * * cd /path/to/menuqr && php artisan schedule:run >> /dev/null 2>&1
```

`demo:reset` refuses to run when `DEMO_MODE=false` unless `--force` is passed.
The command rebuilds the schema, so do not keep real data in the demo database.

## 8. Verification

```powershell
php artisan optimize:clear
php artisan test
php vendor\bin\pint --test
composer audit
npm audit
npm run build
```

Expected: all tests pass, Pint clean, no advisories, 0 npm vulnerabilities,
Vite build succeeds. See [TESTING.md](TESTING.md) for the recorded output.

## 9. Screenshots (optional)

With the server running on port 8104 and a table token from the database:

```powershell
php artisan tinker --execute="echo App\Models\RestaurantTable::query()->first()->token;"
node scripts/screenshots.mjs http://127.0.0.1:8104 <TOKEN>
```

The script uses the installed Chrome at
`C:\Program Files\Google\Chrome\Application\chrome.exe` and writes real
screenshots to `docs/screenshots/`.

## 10. Production notes

This is a demo build. Before production:

- switch to MySQL/PostgreSQL and configure backups;
- set `APP_ENV=production`, `APP_DEBUG=false`, HTTPS and secure cookies;
- use a real mailer for password resets;
- run a queue worker and the scheduler;
- replace the shared demo dataset or turn `DEMO_MODE` off;
- review `docs/SECURITY-REVIEW.md` and `ROADMAP.md`.
