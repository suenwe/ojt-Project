# Knowvia

Laravel 13 application configured to use PostgreSQL.

## Requirements

- PHP 8.3 or later with the `pdo_pgsql` extension enabled
- Composer
- Node.js and npm
- PostgreSQL

In `php.ini`, allow post uploads (up to 10 files of 25 MB each):

```ini
upload_max_filesize = 25M
post_max_size = 260M
```

Restart `php artisan serve` after changing `php.ini`.

## Setup

1. Install PHP dependencies:

   ```powershell
   composer install
   ```

2. Create a PostgreSQL database named `vibely_db` (or choose a different
   name and update `DB_DATABASE` in `.env`):

   ```powershell
   psql -h 127.0.0.1 -U postgres -d postgres -c "CREATE DATABASE vibely_db;"
   ```

3. Create the local environment file and set the PostgreSQL connection details:

   ```powershell
   Copy-Item .env.example .env
   ```

   Set `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in
   `.env` to match your PostgreSQL server. Keep `APP_TIMEZONE=Asia/Yangon`;
   without it, scheduled posts go live at the wrong time.

4. Generate the application key and run the migrations:

   ```powershell
   php artisan key:generate
   php artisan migrate --seed
   ```

5. Build the frontend and start the site:

   ```powershell
   npm install
   npm run build
   php artisan serve
   ```

   Open http://localhost:8000. Sign-up and password reset send a six-digit code
   by email. With the default `MAIL_MAILER=log`, no email is sent: the code is
   written at the end of `storage/logs/laravel.log`. Fill in the `MAIL_*`
   settings in `.env` to send real emails.

After pulling new changes, run `composer install`, `npm install`,
`php artisan migrate`, `php artisan db:seed` and `npm run build`. All of them
are safe to repeat.

The PHPUnit configuration uses an in-memory SQLite database for tests, so the
test suite does not require a running PostgreSQL server.

## Admin console

1. Build the frontend assets:

   ```powershell
   npm install
   npm run build
   ```

2. Create an administrator account from the project terminal:

   ```powershell
   php artisan admin:create
   ```

   Follow the prompts to create a new account with a password, or promote an
   existing member account. This command is terminal-only; public registration
   does not grant administrator access.

3. Sign in at `/admin/login`. The admin console is under `/admin`; all dashboard
   and management routes require an authenticated account with the `admin` role.
   Sign-in is throttled, and banned accounts cannot access web routes.

The overview shows member, pending-report, category, and banned-account totals.
The **User management** section at `/admin?section=users` lists member and admin
accounts, with search by username/email and role/status filters. It displays
profile photo, birthday, latest recognized browser, latest IP, and account status.
Admins can ban or restore member accounts; admin accounts are protected.

The **User access log** section at `/admin?section=access-logs` shows the user,
timestamp, browser, IP address, HTTP method, and requested path for authenticated
web requests. Query-string values are not logged. Access entries remain available
if the associated account is deleted.

The report queue displays the report category, member-provided reason, post,
author, and reporter, including the post's photos and videos. Confirming a
report hides the post (it stays in the database with the state `hidden`) and
suspends its author for three days; dismissing it records an invalid decision
without banning anyone. A member can only have one pending report per post.
While a member is suspended, their posts are hidden from everyone else.

Categories support create, edit, and delete actions.
The original 15 post categories are inserted idempotently by migration. The
additional `Music` category and restricted keywords are included in the
database seeders. Run `php artisan migrate --seed` on a new database, or
`php artisan db:seed` after pulling changes to add the seed data to an existing
database. The seeders are idempotent and do not remove entries managed by admins.
Removing a category leaves its posts uncategorized. Keywords
support add and remove actions and are checked when posts and comments are
submitted.

## Upstream notes
