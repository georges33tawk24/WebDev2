# Database (team setup)

All application data (users, offices, service requests, payments, appointments, chat messages, notifications, feedback, etc.) is stored in a **real SQL database** via Laravel Eloquent. Creating or updating records in the UI calls `create()` / `update()` and writes to disk immediately.

By default Git does **not** ship your personal `database/database.sqlite` (see below). It ships **schema**, **seeders**, and optionally a **team snapshot** you export and commit.

## What lives in the repo

| Path | Purpose |
|------|---------|
| `database/migrations/` | Table definitions (version-controlled schema) |
| `database/seeders/DemoDataSeeder.php` | Lebanon demo data (~160 requests, 8 offices, demo accounts) |
| `database/data/localized_catalog_ar.php` | Arabic catalog strings used when seeding |
| `database/factories/` | Test data factories |
| `database/database.sqlite` | **Not committed** — your private local file |
| `database/dumps/team.sql` | **Optional team snapshot** — commit after `php artisan db:export-team` |
| `database/team.sqlite` | **Optional SQLite snapshot** — commit for teammates on SQLite |

Uploaded files (ID scans, PDFs) go to `storage/app/public/` and are linked at `public/storage/`.

## Why is `database.sqlite` ignored?

`database/database.sqlite` is in `database/.gitignore` on purpose:

- It is a **local scratch file** that changes every time you click around the app.
- Committing it causes **merge conflicts** when two people use the app at once.
- It can accidentally include **password hashes, OAuth tokens, or test payments** you did not mean to share.

**Your live data right now is probably in MySQL** (`DB_CONNECTION=mysql` in `.env`), not in `database.sqlite`. The ignored file may be empty or out of date.

To give teammates **the same rows you have**, export and commit a team snapshot (next section), or keep improving `DemoDataSeeder.php`.

## Share your exact database with teammates

After you have the data you want (requests, payments, users, …):

```bash
# You (once you are happy with your DB)
php artisan db:export-team
git add database/dumps/team.sql database/team.sqlite
git commit -m "Add team database snapshot"
git push
```

Teammates after clone:

```bash
cp .env.example .env
# set DB_CONNECTION=mysql (or sqlite) and DB_* credentials
php artisan db:prepare --import-team
```

That imports `database/dumps/team.sql` (MySQL or SQLite) or copies `database/team.sqlite`, then runs any **newer** migrations.

> Re-export and commit again when you want everyone to pick up your latest data.

## One command (demo seed — baseline, not your personal DB)

From the project root, with `.env` copied from `.env.example`:

```bash
php artisan db:prepare --seed
```

This will:

1. Create `database/database.sqlite` **or** ensure MySQL database `webdev2` exists  
2. Run all migrations  
3. Seed demo data  
4. Create the `public/storage` symlink  

**Reset everything** (wipe local data, reseed):

```bash
php artisan db:prepare --fresh --seed
```

Or use Composer:

```bash
composer setup
```

## Connection options (`.env`)

### SQLite — fastest for new teammates

```env
DB_CONNECTION=sqlite
# DB_DATABASE defaults to database/database.sqlite
```

### MySQL — course / team default

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=webdev2
DB_USERNAME=root
DB_PASSWORD=
```

## Demo logins (after `--seed`)

Password for all: **`password123`**

- `admin@example.com` — platform admin  
- `staff@example.com` — Beirut office staff  
- `citizen@example.com` — citizen with demo ID on file  

## Verify data is saving

```bash
php artisan tinker
>>> \App\Models\User::count();
>>> \App\Models\ServiceRequest::count();
```

After adding a staff user in the admin UI, `User::where('email', 'you@example.com')->exists()` should be `true`.

## Session & queue

`.env.example` uses `SESSION_DRIVER=database` and `QUEUE_CONNECTION=database`, so sessions and queued jobs (2FA email) also persist in the same database.
