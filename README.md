# Community Connect System

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

## Upstream notes

Updated by
Updated by
Update test
