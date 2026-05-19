# WebDev2 — Municipal E-Services Portal

Laravel 13 application for a Lebanon-themed government e-services platform: **citizen portal**, **office staff** workflows, **admin** management, **English/Arabic (RTL)** UI, authentication (2FA, Google/Facebook OAuth), ID upload/OCR, payments, appointments, QR tracking, office chat, citizen feedback, and analytics reports.

**Default integration branch:** `13.x` on GitHub.

---

## Prerequisites

- **PHP 8.3+** with extensions: `pdo`, `pdo_mysql` or `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`
- **Composer**
- **Node.js** and **npm**
- **MySQL 8+** (recommended for teammates) *or* **SQLite** (quick local option)
- **Caddy** (optional, for HTTPS local dev — required for Facebook OAuth)

---

## First-time setup (after cloning from GitHub)

### 1. Install dependencies

```bash
git clone https://github.com/georges33tawk24/WebDev2.git
cd WebDev2
git checkout 13.x
composer install
```

### 2. Environment file

```bash
cp .env.example .env
```

Add shared API keys and mail settings from the team (Discord / `team.env` — **do not commit** `team.env` or `.env`):

```bash
# Option A: paste values from team.env manually into .env
# Option B: append team file (if you have it locally)
cat team.env >> .env
```

Keep the team **`APP_KEY`** in `.env.example` if your group agreed on one shared dev key.

### 3. Git hooks (recommended)

Hooks strip AI `Co-authored-by` trailers so commits stay attributed to **your** git user only:

```bash
git config core.hooksPath .githooks
```

### 4. Database

#### Option A — MySQL (team default)

1. Start MySQL (MAMP, XAMPP, Homebrew, etc.).
2. Create the database:

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS webdev2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

3. In `.env`, set:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=webdev2
DB_USERNAME=root
DB_PASSWORD=your_password
```

#### Option B — SQLite (quick start)

In `.env`, comment out the MySQL block and use:

```env
DB_CONNECTION=sqlite
# DB_DATABASE is optional; defaults to database/database.sqlite
```

Ensure the file exists:

```bash
touch database/database.sqlite
```

### 5. Database (schema + demo data)

Creates tables, seeds **Lebanon-themed demo data** (~160 service requests, 8 municipalities, 23+ services, staff/citizens, appointments, feedback, messages, notifications, demo PDFs, etc.), and links storage:

```bash
php artisan db:prepare --seed
```

**Reset everything** (deletes all local data):

```bash
php artisan db:prepare --fresh --seed
```

See **`database/README.md`** for full detail.

#### Will teammates have the same data as me?

| What they do | What they get |
|--------------|----------------|
| Clone + `php artisan db:prepare --seed` | The **same demo baseline** (seeded accounts, offices, sample requests) — **not** your personal rows from day-to-day use |
| Clone + `php artisan db:prepare --import-team` | The **team SQL snapshot** in `database/dumps/team.sql` (if committed) — same data as whoever last ran export |
| Only `git clone` | **No data** until they run one of the commands above |

- Git ships **migrations** and **seeders**, not your live MySQL database or `database/database.sqlite` (that file is gitignored).
- Each developer has their **own** database (MySQL schema or local SQLite file).
- To share **your exact** current data: run `php artisan db:export-team`, commit `database/dumps/team.sql` (and optional `database/team.sqlite`), push, then teammates run `php artisan db:prepare --import-team`.

> **SQLite quick start:** set `DB_CONNECTION=sqlite` in `.env`, then run `php artisan db:prepare --seed` (creates `database/database.sqlite` automatically).

### 6. Frontend build

```bash
npm install
npm run build
```

### 7. Config cache

```bash
php artisan config:clear
```

### One-command setup

If `.env` is ready and the MySQL database `webdev2` exists:

```bash
composer setup
```

Runs install, `db:prepare --seed`, npm build, and config clear.

---

## Running the application

### HTTPS (Google + Facebook OAuth)

Facebook requires HTTPS on `127.0.0.1`. Install Caddy if needed (`brew install caddy`), then:

```bash
composer dev:https
```

Open: **https://127.0.0.1:8000**

This starts the PHP server, Caddy TLS proxy, a **queue worker** (2FA emails), and **scheduler** (appointment reminders).

**Browser push** needs Chrome to trust Caddy’s local certificate (service workers reject self-signed TLS). Run once:

```bash
caddy trust
```

Enter your Mac password when prompted, then **quit Chrome completely** and reopen the app. Click **Enable notifications** on the yellow banner.

### HTTP only (password login, simpler)

```bash
php artisan serve
php artisan queue:listen
```

Open: **http://127.0.0.1:8000**

---

## Language (English / Arabic)

- Use the **EN | AR** toggle on auth pages and in the top navbar (admin/staff/citizen layouts).
- Locale is stored in the session (`SetLocale` middleware runs **after** session start).
- Translation files: `lang/en/ui.php`, `lang/ar/ui.php` (and `lang/ar/entities.php` for seeded fallbacks).
- Catalog content can be bilingual in the database (`name_ar`, `description_ar`, `municipality_ar`, etc.) — seeded from `database/data/localized_catalog_ar.php`.
- Arabic mode enables **RTL** layout and localized digits/dates via helpers in `app/helpers.php`.

---

## Seeded test accounts

All seeded users use password: **`password123`**

| Role | Email | Notes |
|------|--------|--------|
| Admin | `admin@example.com` | Skips 2FA |
| Staff | `staff@example.com` | Beirut office |
| Staff | `staff.tripoli@example.com`, `staff.saida@example.com`, `staff.baabda@example.com`, … | One per municipality |
| Citizen | `citizen@example.com` | Has demo ID on file |
| Citizen | `citizen.karim@example.com`, `citizen.mira@example.com`, … | 20 citizens total |

Demo data is defined in `database/seeders/DemoDataSeeder.php`.

---

## Main features (by role)

| Area | Highlights |
|------|----------------|
| **Citizen** | Unified sidebar layout; browse/apply for services; track requests; **payments** (Stripe + NOWPayments crypto); **appointments** (email/SMS/push on book + scheduled reminders); office map; **QR code**; **chat**; **feedback**; bilingual receipts |
| **Staff** | Office-scoped requests; status updates with **email alerts**; document download; **catalog** (categories + services for own office); office profile; feedback replies |
| **Admin** | Offices; categories; services; staff + **create citizen** accounts; **`is_active`** activate/deactivate; citizens list; **analytics/reports** (Chart.js, revenue from **paid payments**) |

---

## Viewing the database

### MySQL

```bash
mysql -u root -p webdev2
```

```sql
SHOW TABLES;
SELECT name, municipality FROM offices;
SELECT status, COUNT(*) FROM service_requests GROUP BY status;
```

Or use **TablePlus**, **phpMyAdmin**, or **DBeaver** with your `DB_*` credentials.

### SQLite

Database file: `database/database.sqlite`

```bash
sqlite3 database/database.sqlite
```

Or open that file in **DB Browser for SQLite** / TablePlus.

### Laravel Tinker

```bash
php artisan tinker
```

```php
\App\Models\Office::count();
\App\Models\ServiceRequest::with('citizen', 'service')->latest()->take(5)->get();
```

---

## Stripe card payments (school project)

1. Create a free [Stripe account](https://dashboard.stripe.com/register) (any country works for **test mode**).
2. Developers → API keys → copy **Publishable** and **Secret** test keys into `.env`:
   ```env
   STRIPE_KEY=pk_test_...
   STRIPE_SECRET=sk_test_...
   ```
3. `php artisan config:clear`
4. Log in as `citizen@example.com` → **Payments** → pay for a request → **Pay securely with card (Stripe)**.
5. On Stripe’s page use: `4242 4242 4242 4242`, expiry `12/34`, CVC `123`.

Optional webhook (marks paid even if the user closes the tab before the success URL):

```bash
stripe listen --forward-to https://127.0.0.1:8000/webhooks/stripe
```

Copy the `whsec_...` secret into `STRIPE_WEBHOOK_SECRET`.

---

## Teammate checklist (MySQL)

```bash
git clone https://github.com/georges33tawk24/WebDev2.git
cd WebDev2
git checkout 13.x
git pull origin 13.x
composer install
cp .env.example .env
git config core.hooksPath .githooks
# Edit .env: DB_PASSWORD, paste team.env secrets (never commit .env)
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS webdev2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan db:prepare --seed
# Or match the latest team snapshot from git:
# php artisan db:prepare --import-team
npm install && npm run build
php artisan storage:link
php artisan config:clear
composer dev:https
```

Open **https://127.0.0.1:8000** — log in with `citizen@example.com` / `password123` (see table above).

---

## Important notes

- **Never commit** `.env`, `team.env`, or `database/database.sqlite`.
- **`migrate:fresh --seed`** wipes the database — coordinate before running on a shared machine.
- **OAuth redirect URLs** must match `APP_URL` in `.env` (see `GOOGLE_REDIRECT_URI`, `FACEBOOK_REDIRECT_URI`).
- **ID upload**: Citizens without a valid ID file are redirected to `/id-upload` after login/2FA. Seeded citizens use a demo ID file under `storage/app/public/ids/`.
- **Queue worker** must run for 2FA emails (`queue:listen` or included in `composer dev:https`).
- **Office working hours** in forms are stored as JSON; seeded offices use a structured `days` / `hours` / `note` format.
- **Payments (card)**: [Stripe Checkout](https://stripe.com) in **test mode** — see `.env.example`. Crypto uses [NOWPayments sandbox](https://account-sandbox.nowpayments.io/).
- **SMS** (optional): **Brevo** or **Vonage** (see `.env.example`). Powers appointment alerts and **2FA by SMS** — citizens choose email or SMS at login when configured. Without SMS keys, 2FA is email-only and outbound SMS is logged to `storage/logs/laravel.log`.
- **Browser push**: run `php artisan webpush:vapid`, add keys to `.env`, run `caddy trust` for local HTTPS, then click **Enable notifications** when logged in (Chrome only shows the Allow prompt after that click).
- **Appointment reminders**: `appointments:send-reminders` runs every minute via the scheduler. Local dev: `composer dev:https` starts `schedule:work` automatically. **Production:** add a cron job: `* * * * * cd /path/to/WebDev2 && php artisan schedule:run >> /dev/null 2>&1`
- **Live updates**: notifications and request status badges refresh when staff changes a request. **Local dev** uses light polling (SSE disabled by default — it blocks `php artisan serve`). **Production** can enable SSE with `LIVE_UPDATES_SSE=true` and php-fpm/Octane.

### Site feels slow locally?

Common causes:

1. **SSE + `php artisan serve`** — one long-lived `/api/live/stream` request blocks the single PHP worker. Fixed by default: `LIVE_UPDATES_SSE` is off when `APP_ENV=local`. Run `php artisan config:clear` after changing `.env`.
2. **Heavy dev stack** — `composer dev:https` runs Caddy + `artisan serve` + queue + scheduler; normal for dev, not for production.
3. **`APP_DEBUG=true`** — slower responses and larger error pages.
4. **Large layout** — admin pages include ~1,300 lines of inline CSS plus Google Fonts (first load).
5. **Many tabs open** — each tab polls notifications every 5s when SSE is off.

For a snappier local run: use one browser tab, keep `LIVE_UPDATES_SSE=false`, and run `php artisan config:cache` only when testing production-like settings.
- **Demo documents**: citizen uploads use a real demo PDF; staff-generated PDFs are created for approved/completed requests.

---

## Tests

```bash
php artisan test
```

**114+ automated tests**, including:

- `FullSiteQaTest` — end-to-end flows (requests, staff status, chat, appointments, feedback, admin catalog, role isolation)
- `AuthFlowTest` — login, 2FA, SMS/email channel switch
- `QaSmokeTest` / `NewFeaturesArabicTest` — page loads and Arabic UI
- Payments — `StripeCheckoutTest`, `NowPaymentsCheckoutTest`
- `NotificationAlertsTest`, `LiveUpdateTest`, `AdminReportsRevenueTest`

After clone, run `php artisan storage:link` once (also runs via `composer setup` / `db:prepare`) so document downloads work.

---

## Project structure (high level)

| Area | Path |
|------|------|
| Routes | `routes/web.php` |
| Locale | `app/Http/Middleware/SetLocale.php`, `app/Http/Controllers/LocaleController.php` |
| Helpers (i18n) | `app/helpers.php` |
| Auth & OAuth | `app/Http/Controllers/AuthController.php` |
| Citizen portal | `app/Http/Controllers/Citizen/` |
| Admin | `app/Http/Controllers/Admin/` |
| Staff | `app/Http/Controllers/Staff/` |
| Arabic seed data | `database/data/localized_catalog_ar.php` |
| Seeders | `database/seeders/DemoDataSeeder.php` |
| Main layout | `resources/views/layouts/admin.blade.php` |
| UI components | `resources/views/components/` (`locale-switcher`, `form-page`, …) |

---

## Git workflow

Work on branch **`13.x`** (default on GitHub):

```bash
git checkout 13.x
git pull origin 13.x
```

Configure hooks so commits stay under **your** git user only (no AI `Co-authored-by`):

```bash
git config core.hooksPath .githooks
```

Push:

```bash
git push origin 13.x
```

---

## License

MIT (Laravel framework). See course / team agreement for project ownership.
