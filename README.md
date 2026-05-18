# WebDev2 — Municipal E-Services Portal

Laravel application for a government e-services platform: citizen portal, office staff workflows, admin management, authentication (2FA, Google/Facebook OAuth), ID upload/OCR, payments, appointments, and reports.

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

### 3. Database

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

### 4. Migrations and demo data

Creates all tables and seeds **Lebanon-themed demo data** (offices, services, users, ~70 service requests, etc.):

```bash
php artisan migrate --seed
```

**Reset everything** (deletes all local data):

```bash
php artisan migrate:fresh --seed
```

> Each developer has **their own** database. Git shares **seeders**, not your rows. After clone, everyone runs `migrate --seed` to get the same baseline.

### 5. Frontend build

```bash
npm install
npm run build
```

### 6. Config cache

```bash
php artisan config:clear
```

### One-command setup

If `.env` is ready and the MySQL database `webdev2` exists:

```bash
composer setup
```

Runs install, migrate + seed, npm build, and config clear.

---

## Running the application

### HTTPS (Google + Facebook OAuth)

Facebook requires HTTPS on `127.0.0.1`. Install Caddy if needed (`brew install caddy`), then:

```bash
composer dev:https
```

Open: **https://127.0.0.1:8000**

This starts the PHP server, Caddy TLS proxy, and a queue worker (needed for 2FA email codes).

### HTTP only (password login, simpler)

```bash
php artisan serve
php artisan queue:listen
```

Open: **http://127.0.0.1:8000**

---

## Seeded test accounts

All seeded users use password: **`password123`**

| Role | Email | Notes |
|------|--------|--------|
| Admin | `admin@example.com` | Skips 2FA |
| Staff | `staff@example.com` | Beirut office |
| Staff | `staff.tripoli@example.com`, `staff.saida@example.com`, … | One per municipality |
| Citizen | `citizen@example.com` | Has demo ID on file |
| Citizen | `citizen.karim@example.com`, `citizen.mira@example.com`, … | 20 citizens total |

Demo data is defined in `database/seeders/DemoDataSeeder.php` (8 Lebanese municipalities, 23 services, requests in various statuses).

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

## Teammate checklist (MySQL)

```bash
git clone <repo-url>
cd WebDev2
composer install
cp .env.example .env
# Edit .env: DB_PASSWORD, paste team.env secrets
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS webdev2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --seed
npm install && npm run build
php artisan config:clear
composer dev:https
```

---

## Important notes

- **Never commit** `.env`, `team.env`, or `database/database.sqlite`.
- **`migrate:fresh --seed`** wipes the database — coordinate before running on a shared machine.
- **OAuth redirect URLs** must match `APP_URL` in `.env` (see `GOOGLE_REDIRECT_URI`, `FACEBOOK_REDIRECT_URI`).
- **ID upload**: Citizens without a valid ID file are redirected to `/id-upload` after login/2FA. Seeded citizens use a demo ID file under `storage/app/public/ids/`.
- **Queue worker** must run for 2FA emails (`queue:listen` or included in `composer dev:https`).

---

## Tests

```bash
php artisan test
```

---

## Project structure (high level)

| Area | Path |
|------|------|
| Routes | `routes/web.php` |
| Auth & OAuth | `app/Http/Controllers/AuthController.php` |
| Citizen portal | `app/Http/Controllers/Citizen/` |
| Admin | `app/Http/Controllers/Admin/` |
| Staff | `app/Http/Controllers/Staff/` |
| Seeders | `database/seeders/DemoDataSeeder.php` |
| Main layout | `resources/views/layouts/admin.blade.php` |

---

## License

MIT (Laravel framework). See course / team agreement for project ownership.
