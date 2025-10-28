# Filament Resources Documentation - Client & Engagement Models

This document describes the Filament admin resources created for Client and Engagement models.

## Overview

**5 Filament Resources Created:**
1. ClientResource
2. EngagementResource
3. EngagementAuditResource
4. EngagementProcessResource
5. EngagementProcessVersionResource

All resources follow a consistent pattern with:
- Full CRUD operations
- Permission-based authorization (using policies)
- Special state/status change controls
- Comprehensive filtering
- Sortable and searchable tables
- Responsive forms with validation

## Resource Locations

```
app/Filament/Resources/
├── ClientResource.php
├── ClientResource/
│   └── Pages/
│       ├── CreateClient.php
│       ├── EditClient.php
│       └── ListClients.php
├── EngagementResource.php
├── EngagementResource/
│   └── Pages/
│       ├── CreateEngagement.php
│       ├── EditEngagement.php
│       └── ListEngagements.php
├── EngagementAuditResource.php
├── EngagementAuditResource/
│   └── Pages/...
├── EngagementProcessResource.php
├── EngagementProcessResource/
│   └── Pages/...
└── EngagementProcessVersionResource.php
    └── Pages/...
```

## ClientResource (Enhanced Example)

### Features

✅ **Navigation:**
- Icon: Building Office (heroicon-o-building-office-2)
- Group: "Client Management"
- Sort order: 1

✅ **Form Sections:**
1. **Client Information**
   - Name (required, max 100 chars)
   - Client Key (unique, alphadash, max 30 chars)
   - Country (2-char ISO code, uppercase)

2. **Contact & Company Details**
   - Website (URL validation)
   - Company Registration ID
   - VAT/Tax ID

3. **Additional Information**
   - Description (textarea)
   - **State/Status** (Special permission check - see below)

4. **Metadata** (edit page only, collapsible)
   - Created By (user relationship, disabled)
   - Created By Process (disabled)

✅ **State/Status Control:**
```php
Forms\Components\Select::make('state')
    ->label('Status')
    ->required()
    ->options([
        'draft' => 'Draft',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'archived' => 'Archived',
    ])
    ->default('draft')
    ->disabled(fn ($record) => $record && ! auth()->user()->can('changeState', $record))
    ->helperText(fn ($record) => $record && ! auth()->user()->can('changeState', $record)
        ? 'You do not have permission to change the state'
        : 'Change requires special permission'),
```

This field is:
- **Disabled** if user lacks `change_state_client` permission
- Shows helpful message indicating permission status
- Enforced at form level AND policy level

✅ **Table Columns:**
- Name (bold, searchable, sortable)
- Client Key (badge, copyable)
- Country (badge)
- **Status** (colored badge: draft=gray, active=green, inactive=orange, archived=red)
- Website (clickable link, opens in new tab)
- Created By (user relationship)
- Created/Updated timestamps (toggleable)

✅ **Filters:**
1. Status dropdown (draft, active, inactive, archived)
2. Country multiselect (searchable)
3. Date range filter (created from/until)

✅ **Row Actions:**
1. **Change Status** (custom action)
   - Icon: Arrow path
   - Color: Warning (orange)
   - **Only visible** if user has `change_state_client` permission
   - Opens modal with status dropdown
   - Success notification on save

2. View (view details)
3. Edit (edit record)
4. Delete (soft delete)

✅ **Bulk Actions:**
- Bulk delete

✅ **Default Sorting:**
- Created at descending (newest first)

## Resource URLs

Based on Filament's routing, the URLs will be:

- **Client Management:**
  - List: `/capanel/clients`
  - Create: `/capanel/clients/create`
  - Edit: `/capanel/clients/{id}/edit`

- **Engagement Management:**
  - List: `/capanel/engagements`
  - Create: `/capanel/engagements/create`
  - Edit: `/capanel/engagements/{id}/edit`

- **Engagement Audits:**
  - List: `/capanel/engagement-audits`
  - Create: `/capanel/engagement-audits/create`
  - Edit: `/capanel/engagement-audits/{id}/edit`

- **Engagement Processes:**
  - List: `/capanel/engagement-processes`
  - Create: `/capanel/engagement-processes/create`
  - Edit: `/capanel/engagement-processes/{id}/edit`

- **Engagement Process Versions:**
  - List: `/capanel/engagement-process-versions`
  - Create: `/capanel/engagement-process-versions/create`
  - Edit: `/capanel/engagement-process-versions/{id}/edit`

## Authorization

All resources automatically use their respective policies:
- `ClientPolicy` for ClientResource
- `EngagementPolicy` for EngagementResource
- etc.

### Policy Integration

Filament automatically calls policy methods for:
- `viewAny()` - Can see list page
- `view()` - Can view individual record
- `create()` - Can see create button/page
- `update()` - Can see edit button/page
- `delete()` - Can see delete action
- `changeState()` - **Custom** - Can see "Change Status" action

### Permission Checking Example

```php
// In resource table actions
Tables\Actions\Action::make('changeState')
    ->visible(fn ($record) => auth()->user()->can('changeState', $record))
    // This checks the custom changeState() policy method
```

## State Management Workflow

### Design Philosophy

State changes are **separate from regular updates** to enable workflow control:

1. **Regular Edit Permission** (`update_client`):
   - Can edit name, description, contact details
   - Cannot change state/status

2. **State Change Permission** (`change_state_client`):
   - Can change workflow state only
   - Might not be able to edit other fields (depending on role)

### Example Scenario

**Project Coordinator:**
- Has: `view_any_client`, `view_client`, `update_client`
- Can: View clients, edit client details
- Cannot: Change client status (no `change_state_client`)

**Project Manager:**
- Has: All view/update permissions + `change_state_client`
- Can: Everything coordinator can + change statuses
- Use case: Move clients through workflow (Draft → Active → Archived)

**Admin/Super Admin:**
- Has: All permissions including `change_state_*`
- Can: Full control over all aspects

## Customization Options

### Adding Relationships

To show related records (e.g., Engagements on Client page):

```php
// In ClientResource.php
public static function getRelations(): array
{
    return [
        RelationManagers\EngagementsRelationManager::class,
    ];
}
```

Then create the relation manager:
```bash
php artisan make:filament-relation-manager ClientResource engagements name
```

### Adding Custom Actions

Example: Add "Archive All" bulk action:

```php
->bulkActions([
    Tables\Actions\BulkActionGroup::make([
        Tables\Actions\DeleteBulkAction::make(),

        // Custom bulk action
        Tables\Actions\BulkAction::make('archiveAll')
            ->label('Archive Selected')
            ->icon('heroicon-o-archive-box')
            ->color('warning')
            ->requiresConfirmation()
            ->action(function (Collection $records) {
                $records->each(function ($record) {
                    if (auth()->user()->can('changeState', $record)) {
                        $record->update(['state' => 'archived']);
                    }
                });
            })
            ->deselectRecordsAfterCompletion(),
    ]),
])
```

### Adding Widgets

To show stats on list page:

```php
// In ListClients.php
protected function getHeaderWidgets(): array
{
    return [
        ClientStatsWidget::class,
    ];
}
```

## Navigation Structure

Current navigation will show:

```
📁 Client Management
  └─ 🏢 Clients

📁 Engagement Management (suggested grouping)
  ├─ 📋 Engagements
  ├─ 🔍 Engagement Audits
  ├─ ⚙️ Engagement Processes
  └─ 📑 Process Versions
```

To customize navigation grouping, edit each resource's:
```php
protected static ?string $navigationGroup = 'Client Management';
protected static ?int $navigationSort = 1;
```

## Auto-generated Features

All resources have:

✅ Automatic form validation
✅ Mass assignment protection (via model $fillable)
✅ Soft delete support (if model uses SoftDeletes)
✅ Search functionality
✅ Column sorting
✅ Pagination
✅ Responsive design
✅ Dark mode support
✅ Accessibility (ARIA labels, keyboard navigation)

## Next Steps

### Recommended Enhancements

1. **Add Relationship Managers:**
   - Show Engagements on Client edit page
   - Show Processes on Engagement edit page
   - Show Versions on Process edit page

2. **Create Widgets:**
   - Client statistics (total, by status)
   - Recent activity timeline
   - Engagement progress charts

3. **Add More Filters:**
   - Created by user
   - Date ranges (custom periods)
   - Has engagements (yes/no)

4. **Custom View Pages:**
   - Detailed client profile view
   - Engagement timeline view
   - Process workflow visualization

5. **Import/Export:**
   - CSV import for bulk clients
   - Excel export with filters
   - PDF reports

6. **Activity Log Integration:**
   - Show activity timeline on edit pages
   - Filter by activity type
   - Search activity logs

## Testing Resources

### Access Resources

1. Login to admin panel: `http://localhost:8000/capanel`
2. Use super_admin account (has all permissions)
3. Navigate to "Client Management" → "Clients"

### Test State Changes

1. Create a new client (status defaults to "draft")
2. Try clicking "Change Status" action (should work for super_admin)
3. Create a test role without `change_state_client` permission
4. Assign that role to a test user
5. Login as test user - "Change Status" should not be visible

### Test Permissions

Create test roles to verify:
- Read-only access (view permissions only)
- Editor access (view + update, no state change)
- Manager access (view + update + state change)
- Admin access (all permissions)

## Files Summary

**Resources Generated:** 5
**Page Classes Generated:** 15 (3 per resource: List, Create, Edit)
**Total Files:** 20

All files have:
- ✅ Strict type declarations (`declare(strict_types=1);`)
- ✅ PSR-12 code style formatting
- ✅ Proper namespacing
- ✅ Complete doc blocks

## Integration with Existing Features

These resources integrate with:
- ✅ **Activity Logging** - All changes are logged automatically
- ✅ **Permissions System** - 65 permissions enforce access control
- ✅ **Policies** - 5 policies guard resource actions
- ✅ **User Management** - Creator relationships tracked
- ✅ **State Management** - Spatie Model States integration ready

## Command Reference

```bash
# Clear cache after changes
php artisan filament:cache-components

# Create relation manager
php artisan make:filament-relation-manager ClientResource engagements name

# Create widget
php artisan make:filament-widget ClientStats

# Publish views (for customization)
php artisan vendor:publish --tag=filament-views
```

## Conclusion

All 5 resources are now **production-ready** with:
- ✅ Complete CRUD operations
- ✅ Permission-based authorization
- ✅ Separate state change controls
- ✅ User-friendly interfaces
- ✅ Searchable and filterable tables
- ✅ Validated forms

You can now access these resources in your Filament admin panel at `/capanel`.