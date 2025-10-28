# Activity Logging Documentation

This document describes how to use activity logging for Client and Engagement models in the DLogic AIA Platform.

## Overview

All 5 models (Client, Engagement, EngagementAudit, EngagementProcess, EngagementProcessVersion) now have comprehensive activity logging using `spatie/laravel-activitylog`.

## Features

- **Automatic logging** - All create, update, delete operations are logged automatically
- **User tracking** - Changes by authenticated users are tracked automatically
- **Process/Automation logging** - Custom methods to log changes by automated processes
- **System logging** - Methods to log system-triggered changes
- **Custom properties** - Ability to attach arbitrary JSON data to log entries
- **Only dirty tracking** - Only changed attributes are logged (saves space)
- **Empty log prevention** - No logs created when nothing actually changes

## Basic Usage

### Automatic Logging

When authenticated users make changes, they're logged automatically:

```php
use App\Models\Client;

// User is authenticated
auth()->login($user);

// This change is automatically logged with the user as causer
$client = Client::create([
    'name' => 'New Client',
    'clinet_key' => 'client-001',
    'country' => 'US',
]);

// Update is also logged automatically
$client->update(['name' => 'Updated Client']);
```

### Logging Automation/Process Changes

When changes are made by a background job or automated process:

```php
$client->logActivityByProcess(
    'imported from external API',
    'ClientImportJob',
    [
        'api_id' => 12345,
        'source' => 'CRM',
        'batch_id' => 'batch-2025-001'
    ]
);
```

**Without additional data:**
```php
$client->logActivityByProcess('synchronized', 'DataSyncProcess');
```

### Logging System Changes

When changes are triggered by the system (cron jobs, scheduled tasks, etc.):

```php
$client->logActivityBySystem(
    'auto-archived due to inactivity',
    [
        'trigger' => 'scheduled_task',
        'days_inactive' => 90,
        'task_id' => 'archive-inactive-clients'
    ]
);
```

**Without additional data:**
```php
$engagement->logActivityBySystem('status changed by scheduler');
```

### Logging User Changes with Extra Context

When you need to log user actions with additional metadata:

```php
$client->logActivityByUser(
    'updated via API',
    $user,
    [
        'ip_address' => request()->ip(),
        'endpoint' => '/api/clients/123',
        'request_id' => 'req-abc-123',
        'user_agent' => request()->userAgent()
    ]
);
```

### Logging with Completely Custom Properties

For maximum flexibility with any context data:

```php
$engagement->logActivityWithProperties(
    'webhook received',
    [
        'webhook_id' => 'wh_12345',
        'provider' => 'stripe',
        'event' => 'payment.succeeded',
        'payload' => ['amount' => 5000, 'currency' => 'usd']
    ]
);

// With a causer (user)
$client->logActivityWithProperties(
    'approved for premium tier',
    [
        'tier' => 'premium',
        'approved_by' => 'manager',
        'notes' => 'Meets all criteria'
    ],
    $user
);
```

## Retrieving Activity Logs

### Get all activities for a model

```php
use Spatie\Activitylog\Models\Activity;

// Get all activities for a specific client
$activities = Activity::forSubject($client)->get();

// Or use the relationship (if configured)
$activities = $client->activities;
```

### Filter by causer (who made the change)

```php
// Changes made by a specific user
$activities = Activity::causedBy($user)->get();

// Changes made by automation (no causer)
$activities = Activity::whereNull('causer_id')->get();
```

### Filter by source type

```php
// Process/automation changes
$activities = Activity::where('properties->source', 'process')->get();

// System changes
$activities = Activity::where('properties->source', 'system')->get();

// User changes
$activities = Activity::where('properties->source', 'user')->get();
```

### Get changes with properties

```php
$activity = Activity::first();

// Get the description
echo $activity->description; // "updated via API"

// Get properties
$properties = $activity->properties;
echo $properties['source']; // "user"
echo $properties['data']['ip_address']; // "192.168.1.1"

// Get changed attributes
$changes = $activity->changes();
$oldValues = $changes['old'];
$newValues = $changes['attributes'];
```

## Logged Attributes

### Client
- name
- clinet_key
- country
- website
- company_gid
- company_vat_gid
- description
- state

### Engagement
- key
- name
- client_fk
- version
- description
- data
- state

### EngagementAudit
- engagement_fk
- name
- type
- data
- description
- state

### EngagementProcess
- engagement_fk
- key
- name
- description
- data
- state

### EngagementProcessVersion
- process_fk
- name
- version_number
- description
- data
- state

## Activity Log Table Structure

Activities are stored in the `activity_log` table with these key columns:

- `id` - Primary key
- `log_name` - Optional log categorization (default: "default")
- `description` - Description of the activity (e.g., "created", "updated", "imported from API")
- `subject_type` - Model class (e.g., "App\Models\Client")
- `subject_id` - ID of the model
- `causer_type` - User model class (nullable)
- `causer_id` - User ID (nullable for system/automation)
- `properties` - JSON column with:
  - `attributes` - New values
  - `old` - Old values (for updates)
  - `source` - "user", "process", or "system"
  - `process_name` - Name of the automation process (if applicable)
  - `data` - Custom context data
- `created_at` - When the activity occurred

## Example Queries

### Recent changes to a client
```php
$recentChanges = Activity::forSubject($client)
    ->latest()
    ->take(10)
    ->get();
```

### All imports from a specific process
```php
$imports = Activity::where('properties->source', 'process')
    ->where('properties->process_name', 'ClientImportJob')
    ->get();
```

### Changes in the last 24 hours
```php
$todayActivities = Activity::where('created_at', '>=', now()->subDay())
    ->get();
```

### System-triggered archives
```php
$archives = Activity::whereNull('causer_id')
    ->where('description', 'like', '%archive%')
    ->where('properties->source', 'system')
    ->get();
```

## Testing

Tests are located in `tests/Unit/LogsActivityWithContextTest.php` and verify:
- ✓ Process logging with/without data
- ✓ System logging with/without data
- ✓ User logging with/without data
- ✓ Custom properties with/without causer
- ✓ Automatic logging
- ✓ Trait methods exist

Run tests with:
```bash
php artisan test --filter=LogsActivityWithContextTest
```

## Notes

- Activity logs are stored indefinitely by default
- Consider implementing a cleanup policy for old logs
- The trait `LogsActivityWithContext` is in `app/Traits/LogsActivityWithContext.php`
- All 5 models use both `LogsActivity` and `LogsActivityWithContext` traits
- Database migrations for Client/Engagement models skip on SQLite (for testing)