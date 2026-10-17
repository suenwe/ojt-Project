# Vibely

Laravel 13 application configured to use PostgreSQL.

## Requirements

- PHP 8.3 or later with the `pdo_pgsql` extension enabled
- Composer
- PostgreSQL

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
   `.env` to match your PostgreSQL server.

4. Generate the application key and run the migrations:

   ```powershell
   php artisan key:generate
   php artisan migrate
   ```

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
author, and reporter. Confirming a report records the review and bans the post
author; dismissing it records an invalid decision without banning anyone.

Categories and notification templates support create, edit, and delete actions.
The original 15 post categories are inserted idempotently by migration and shown
in the category manager after `php artisan migrate`.
Removing a category leaves its posts uncategorized; deleting a notification
template also deletes its associated delivered notification records. Keywords
support add and remove actions. This repository does not yet contain a post
publishing/validation flow, so the keyword list is maintained in the
database but is not currently enforced during post submission.

## Upstream notes
