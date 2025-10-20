# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**DLogic AIA Platform** - Laravel 12 admin platform with Filament UI, built for role-based access control and activity tracking.

**Tech Stack:**
- Laravel 12.0 (PHP 8.2+)
- Filament v3 (Admin panel framework)
- Vite 7 + TailwindCSS 4 (Frontend)
- MySQL (Production), SQLite (Testing)

**Key Packages:**
- `filament/filament` - Modern admin panel UI
- `bezhansalleh/filament-shield` - RBAC for Filament
- `spatie/laravel-permission` - Role & permission management
- `spatie/laravel-activitylog` - Audit trail logging
- `spatie/laravel-model-states` - State machine pattern
- `laravel/fortify` - Authentication scaffolding

## Development Commands

### Setup & Installation
```bash
composer setup              # Full setup: install deps, key gen, migrate, build
composer install            # Install PHP dependencies
npm install                 # Install frontend dependencies
cp .env.example .env        # Create environment file
php artisan key:generate    # Generate app key
php artisan migrate         # Run migrations
```

### Development Server
```bash
npm run dev                 # Starts all dev services concurrently:
                           # - Artisan serve (localhost:8000)
                           # - Queue listener
                           # - Pail log streaming
                           # - Vite dev server (HMR)

# Or run individually:
php artisan serve          # Web server only
php artisan queue:listen   # Queue worker
php artisan pail           # Real-time logs
```

### Testing
```bash
composer test              # Clear config cache + run PHPUnit
php artisan test           # Run all tests with Laravel's test runner
php artisan test --filter=TestName  # Run specific test
```

### Code Quality
```bash
vendor/bin/pint            # Format all code (PSR-12)
vendor/bin/pint --test     # Check formatting without changes
vendor/bin/pint app/Models # Format specific directory
```

### Database
```bash
php artisan migrate                    # Run pending migrations
php artisan migrate:fresh --seed       # Fresh database with test data
php artisan db:seed                    # Seed database only
php artisan make:migration create_x_table  # Create migration
php artisan make:model ModelName -mfs  # Model + migration, factory, seeder
```

### Frontend
```bash
npm run build              # Production build
npm run dev                # Development with HMR
```

## Code Architecture

### Strict PHP Configuration
**All PHP files MUST use strict types:**
```php
<?php

declare(strict_types=1);
```
This is enforced by Laravel Pint. All new files should follow this pattern.

### Authentication & Authorization Flow
- **Guard:** Session-based (`web`)
- **User Provider:** Eloquent (`App\Models\User`)
- **RBAC System:** Spatie Laravel Permission
  - Roles & permissions stored in database
  - Middleware: `role:admin`, `permission:edit posts`
  - Check in code: `$user->hasRole('admin')`, `$user->can('edit posts')`

- **Filament Shield:** Resource-based permissions for admin panel
  - Auto-generates permissions for Filament resources
  - Integrates with Spatie permissions

### Database Patterns
- **All tables use database-backed storage:** sessions, cache, queue jobs
- **Migrations:** Anonymous class format (Laravel 12 standard)
- **Factories:** Use for test data generation
- **Seeders:** `DatabaseSeeder` creates test@example.com user by default

### Testing Environment
- **Database:** In-memory SQLite (`:memory:`)
- **Queue:** Synchronous (`sync` - no background processing)
- **Cache/Session:** Array driver (ephemeral)
- **Bcrypt rounds:** 4 (faster tests)
- **No external dependencies required for tests**

### Frontend Architecture
**Vite Configuration:**
- Entry points: `resources/css/app.css`, `resources/js/app.js`
- TailwindCSS is imported directly in CSS: `@import 'tailwindcss'`
- Custom font: "Instrument Sans" via Google Bunny Fonts
- Dark mode support via `@media (prefers-color-scheme: dark)`

**Blade Templates:**
- Base template: `resources/views/welcome.blade.php`
- Uses `@vite()` directive for asset loading
- Conditional rendering for authenticated vs guest users

### Queue System
- **Driver:** Database (`jobs`, `job_batches`, `failed_jobs` tables)
- **Development:** Run `php artisan queue:listen` (included in `npm run dev`)
- **Production:** Use `php artisan queue:work` with supervisor
- **Failed jobs:** Check with `php artisan queue:failed`

## Filament Admin Panel

### Admin Panel Access
- **URL:** `http://localhost:8000/capanel` (secure, non-guessable path)
- **Default Admin Login:**
  - Email: `admin@dlogic.com`
  - Password: `password`

### Resource Creation
```bash
php artisan make:filament-resource ModelName --generate --soft-deletes
```
This creates:
- Resource class in `app/Filament/Resources/`
- CRUD pages (List, Create, Edit)
- Auto-generates form/table from model structure

### Shield Permissions
Seed permissions and roles:
```bash
php artisan db:seed --class=ShieldSeeder
```
This creates:
- `super_admin` role with all permissions
- `admin` role with limited permissions
- `user` role with view-only permissions

### User Resource Features
The User Resource (`app/Filament/Resources/UserResource.php`) includes:
- **Table Columns:** Name, Email, Roles (badges), Email Verified status
- **Forms:** Name, Email, Password, Roles (multiselect via Spatie)
- **Filters:** Role filter, Email verified, Soft deleted (trashed)
- **Actions:** Edit, Delete (soft), Restore, Force Delete
- **Activity Timeline Widget:** Shows user activity history on edit page
- **Policy:** UserPolicy prevents self-deletion and enforces permissions

### Typical Filament Resource Structure
```php
app/Filament/Resources/
├── UserResource.php              # Main resource definition
└── UserResource/
    └── Pages/
        ├── ListUsers.php         # Index page
        ├── CreateUser.php        # Create page
        └── EditUser.php          # Edit page
```

## Common Development Patterns

### Creating a New Model with RBAC
```bash
# 1. Create model with migration, factory, seeder
php artisan make:model Post -mfs

# 2. Define migration structure
# Edit: database/migrations/*_create_posts_table.php

# 3. Run migration
php artisan migrate

# 4. Create Filament resource
php artisan make:filament-resource Post --generate

# 5. Generate permissions
php artisan shield:generate
```

### Activity Logging (Spatie)
Models can log activities automatically:
```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Post extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'content'])
            ->logOnlyDirty();
    }
}
```

### State Management (Spatie Model States)
For models with state transitions:
```php
use Spatie\ModelStates\HasStates;

class Order extends Model
{
    use HasStates;

    protected $casts = [
        'status' => OrderStatus::class,
    ];
}
```

## Code Style Enforcement

**Pint Configuration (pint.json):**
- PSR-12 standard
- Strict types declaration required
- Trailing commas in multiline arrays
- Proper PHPDoc formatting
- Excluded: `vendor/`, `node_modules/`, `storage/`, `public/`

**Run before committing:**
```bash
vendor/bin/pint
```

## Environment Configuration

### Required .env Variables
```
APP_KEY=                    # Generated by `php artisan key:generate`
DB_CONNECTION=mysql         # Use 'mysql' for dev/prod
DB_DATABASE=dlogic_aia_platform
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database     # Uses sessions table
CACHE_STORE=database        # Uses cache table
QUEUE_CONNECTION=database   # Uses jobs table
```

### Storage Symlink
```bash
php artisan storage:link    # Creates public/storage → storage/app/public
```

## Debugging

### Real-time Logs
```bash
php artisan pail            # Stream logs in real-time
php artisan pail --filter=error  # Filter by level
```

### Query Debugging
In tinker or code:
```php
DB::enableQueryLog();
// ... run queries ...
dd(DB::getQueryLog());
```

### Filament Panel Access
Admin panel route: `/capanel` (secure, non-guessable path)

## Important Notes

- **All database drivers are database-backed:** sessions, cache, queue. This requires running migrations before the app will work.
- **Tests use SQLite in-memory:** No need for separate test database setup.
- **Strict type declarations:** Enforced by Pint on all PHP files.
- **Bcrypt rounds:** 12 in production (secure), 4 in testing (fast).
- **Concurrent dev server:** `npm run dev` starts web server, queue, logs, and Vite simultaneously.