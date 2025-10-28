# Permissions Documentation - Client & Engagement Models

This document describes the permission structure for the 5 new models: Client, Engagement, EngagementAudit, EngagementProcess, and EngagementProcessVersion.

## Overview

A comprehensive permission system has been implemented with **65 total permissions** across 5 models, including a special **change_state** permission that's separate from regular edit permissions.

## Why Separate State/Status Permissions?

The `change_state` permission is **separate from `update`** to allow granular control over who can change model states/statuses. This is critical because:

- States will be used for Kanban boards and project management workflows
- Not everyone who can edit a record should be able to change its status
- Prevents unauthorized workflow progression (e.g., junior staff marking items as "completed")
- Allows audit trail of state changes with proper authorization

## Permission Structure

### Standard Permissions (12 per model)

Each model has these standard Filament Shield permissions:

1. `view_any_{model}` - Can view list of records
2. `view_{model}` - Can view individual record details
3. `create_{model}` - Can create new records
4. `update_{model}` - Can edit record data (name, description, etc.)
5. `delete_{model}` - Can soft delete a record
6. `delete_any_{model}` - Can bulk delete records
7. `restore_{model}` - Can restore soft-deleted records
8. `restore_any_{model}` - Can bulk restore records
9. `force_delete_{model}` - Can permanently delete a record
10. `force_delete_any_{model}` - Can bulk force delete
11. `replicate_{model}` - Can duplicate/clone records
12. `reorder_{model}` - Can reorder records in lists

### Custom Permission (1 per model)

13. `change_state_{model}` - **CUSTOM** Can change the state/status field

## Models and Permission Names

### 1. Client
**Permission suffix:** `client`

- `view_any_client`
- `view_client`
- `create_client`
- `update_client`
- **`change_state_client`** ← Can change client state
- `delete_client`
- `delete_any_client`
- `restore_client`
- `restore_any_client`
- `force_delete_client`
- `force_delete_any_client`
- `replicate_client`
- `reorder_client`

### 2. Engagement
**Permission suffix:** `engagement`

- `view_any_engagement`
- `view_engagement`
- `create_engagement`
- `update_engagement`
- **`change_state_engagement`** ← Can change engagement state
- `delete_engagement`
- `delete_any_engagement`
- `restore_engagement`
- `restore_any_engagement`
- `force_delete_engagement`
- `force_delete_any_engagement`
- `replicate_engagement`
- `reorder_engagement`

### 3. EngagementAudit
**Permission suffix:** `engagement::audit`

- `view_any_engagement::audit`
- `view_engagement::audit`
- `create_engagement::audit`
- `update_engagement::audit`
- **`change_state_engagement::audit`** ← Can change audit state
- `delete_engagement::audit`
- `delete_any_engagement::audit`
- `restore_engagement::audit`
- `restore_any_engagement::audit`
- `force_delete_engagement::audit`
- `force_delete_any_engagement::audit`
- `replicate_engagement::audit`
- `reorder_engagement::audit`

### 4. EngagementProcess
**Permission suffix:** `engagement::process`

- `view_any_engagement::process`
- `view_engagement::process`
- `create_engagement::process`
- `update_engagement::process`
- **`change_state_engagement::process`** ← Can change process state
- `delete_engagement::process`
- `delete_any_engagement::process`
- `restore_engagement::process`
- `restore_any_engagement::process`
- `force_delete_engagement::process`
- `force_delete_any_engagement::process`
- `replicate_engagement::process`
- `reorder_engagement::process`

### 5. EngagementProcessVersion
**Permission suffix:** `engagement::process::version`

- `view_any_engagement::process::version`
- `view_engagement::process::version`
- `create_engagement::process::version`
- `update_engagement::process::version`
- **`change_state_engagement::process::version`** ← Can change version state
- `delete_engagement::process::version`
- `delete_any_engagement::process::version`
- `restore_engagement::process::version`
- `restore_any_engagement::process::version`
- `force_delete_engagement::process::version`
- `force_delete_any_engagement::process::version`
- `replicate_engagement::process::version`
- `reorder_engagement::process::version`

## Policies

Each model has a Policy class that enforces these permissions:

- `app/Policies/ClientPolicy.php`
- `app/Policies/EngagementPolicy.php`
- `app/Policies/EngagementAuditPolicy.php`
- `app/Policies/EngagementProcessPolicy.php`
- `app/Policies/EngagementProcessVersionPolicy.php`

### Custom Method: `changeState()`

Each policy includes a custom `changeState()` method:

```php
public function changeState(User $user, Client $client): bool
{
    return $user->can('change_state_client');
}
```

This method can be used in your code to check if a user can change states:

```php
// In controllers/resources
if ($user->can('changeState', $client)) {
    // Allow state change
    $client->state = $newState;
    $client->save();
}
```

## Seeding Permissions

Permissions are seeded using:

```bash
php artisan db:seed --class=EngagementModelsPermissionsSeeder
```

The seeder:
- Creates all 65 permissions (13 per model × 5 models)
- Automatically assigns all permissions to the `super_admin` role
- Shows a detailed summary of created permissions
- Can be run multiple times (idempotent)

## Role Assignment

### Current Roles

Three roles exist by default:
1. **super_admin** - Has ALL permissions (including all 65 new ones)
2. **admin** - Has limited User management permissions only
3. **user** - Has view permission for their own user record

### Assigning Permissions to Roles

You can assign permissions via Filament's Shield UI or programmatically:

```php
use Spatie\Permission\Models\Role;

$role = Role::findByName('admin');

// Assign all Client permissions to admin
$role->givePermissionTo([
    'view_any_client',
    'view_client',
    'create_client',
    'update_client',
    // but NOT change_state_client - keep that for super_admin only
]);
```

### Example Permission Scenarios

**Scenario 1: Junior Staff**
- `view_any_client`, `view_client` - Can browse and view clients
- `update_client` - Can edit client details
- ❌ No `change_state_client` - Cannot change workflow states

**Scenario 2: Project Manager**
- All view and update permissions
- ✅ `change_state_client`, `change_state_engagement` - Can move items through workflow
- ❌ No delete/force_delete - Cannot remove records

**Scenario 3: Admin**
- All permissions including `change_state_*`
- Full CRUD access
- Can manage record lifecycle

## Checking Permissions in Code

### In Controllers/Services

```php
// Check if user can edit
if ($user->can('update_client')) {
    // Allow editing
}

// Check if user can change state
if ($user->can('change_state_client')) {
    // Allow state changes
}

// Using policies
if ($user->can('update', $client)) {
    // Regular update
}

if ($user->can('changeState', $client)) {
    // State change
}
```

### In Filament Resources

Filament automatically checks policy methods. You'll implement state change controls in the Filament resources when we create them.

## Viewing Current Permissions

View all permissions and role assignments:

```bash
php artisan permission:show
```

This displays a table showing which roles have which permissions.

## Database Tables

Permissions are stored in these Spatie Permission tables:
- `permissions` - All permission definitions
- `roles` - Role definitions
- `model_has_permissions` - Direct user → permission assignments
- `model_has_roles` - User → role assignments
- `role_has_permissions` - Role → permission assignments

## Summary

✅ **65 permissions created** (13 per model × 5 models)
✅ **5 policies created** with standard + custom `changeState()` method
✅ **All permissions assigned to super_admin**
✅ **Separate state change permission** for granular workflow control
✅ **Seeder created** for easy permission deployment

## Next Steps

When creating Filament resources:
1. Resources will automatically use these policies
2. Implement UI controls for state changes that check `changeState` permission
3. Create role-specific views if needed
4. Add custom actions that respect the `change_state_*` permissions