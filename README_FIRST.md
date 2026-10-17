# Vibely UI Handoff

## Branch

UI changes are available on:

```text
origin/UI
```

## Get the UI branch

Before switching branches, save any local work:

```powershell
git status
git add <your-files>
git commit -m "Save local work"
```

Fetch the remote branch:

```powershell
git fetch origin
```

Create a local tracking branch:

```powershell
git switch --track -c UI origin/UI
```

If the local `UI` branch already exists:

```powershell
git switch UI
git pull --ff-only origin UI
```

## Merge the UI into another feature branch

Switch to the feature branch that should receive the UI:

```powershell
git switch <your-feature-branch>
git fetch origin
git merge origin/UI
```

If conflicts occur:

```powershell
git status
```

Resolve the marked files, then run:

```powershell
git add <resolved-files>
git commit
```

Do not use `git reset --hard` while resolving conflicts.

## Included UI

- Authenticated Vibely feed shell
- Authentication landing page with sign-in, registration, and two-factor placeholders
- Basic admin dashboard
- Shared Blade layout
- Responsive light/dark styling
- Account settings and menu controls
- Search, post, schedule, sorting, category, and hashtag UI controls
- Database session migration
- Temporary preview routes

## Local setup

Use a local `.env` file and local PostgreSQL database.

Install dependencies if needed:

```powershell
composer install
npm install
```

Run migrations and build assets:

```powershell
php artisan migrate
npm run build
```

Clear stale cached configuration and views:

```powershell
php artisan config:clear
php artisan view:clear
```

## Preview the UI

Start Laravel:

```powershell
php artisan serve
```

Open:

```text
http://127.0.0.1:8000/auth-preview
http://127.0.0.1:8000/admin-preview
```

The preview routes do not authenticate users or protect the admin page.

## Required backend integration

Replace the preview routes with production routes:

- `/` displays the authentication landing page.
- Regular users are redirected to the authenticated Vibely feed.
- Admin users are redirected to the admin dashboard.
- Two-factor verification is displayed only when required.
- The admin dashboard is protected with authentication and admin authorization middleware.
- The form `action` values in the Blade templates are connected to the relevant controller routes.
- The temporary `/auth-preview` and `/admin-preview` routes are removed after integration.

## UI files

```text
resources/views/auth_ui/landing.blade.php
resources/views/admin_ui/dashboard.blade.php
resources/views/layouts/app.blade.php
resources/views/welcome.blade.php
resources/css/app.css
vite.config.js
routes/web.php
database/migrations/2026_10_16_154008_create_sessions_table.php
```

The `.env`, `vendor/`, `node_modules/`, and old prototype files are not included.
