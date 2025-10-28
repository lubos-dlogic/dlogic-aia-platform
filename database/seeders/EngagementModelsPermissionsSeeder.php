<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EngagementModelsPermissionsSeeder extends Seeder
{
    /**
     * The models to create permissions for.
     */
    private array $models = [
        'client',
        'engagement',
        'engagement::audit',
        'engagement::process',
        'engagement::process::version',
    ];

    /**
     * Standard permission prefixes (from Filament Shield config).
     */
    private array $standardPrefixes = [
        'view_any',
        'view',
        'create',
        'update',
        'delete',
        'delete_any',
        'restore',
        'restore_any',
        'force_delete',
        'force_delete_any',
        'replicate',
        'reorder',
    ];

    /**
     * Custom permission prefixes specific to our requirements.
     */
    private array $customPrefixes = [
        'change_state', // Custom: separate permission for changing state/status
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Creating permissions for Client and Engagement models...');

        $allPrefixes = array_merge($this->standardPrefixes, $this->customPrefixes);
        $createdCount = 0;

        foreach ($this->models as $model) {
            $this->command->info("Processing model: {$model}");

            foreach ($allPrefixes as $prefix) {
                $permissionName = "{$prefix}_{$model}";

                // Create permission if it doesn't exist
                if (! Permission::where('name', $permissionName)->exists()) {
                    Permission::create([
                        'name' => $permissionName,
                        'guard_name' => 'web',
                    ]);
                    $createdCount++;
                    $this->command->line("  ✓ Created: {$permissionName}");
                } else {
                    $this->command->line("  - Already exists: {$permissionName}");
                }
            }
        }

        $this->command->newLine();
        $this->command->info("✓ Created {$createdCount} new permissions");
        $this->command->newLine();

        // Assign all permissions to super_admin role if it exists
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $this->command->info('Assigning all new permissions to super_admin role...');
            $permissions = Permission::whereIn('name', function ($query) {
                $query->selectRaw("CONCAT(prefix, '_', model) as name")
                    ->fromRaw('(SELECT ? as prefix, ? as model) as perms', [
                        implode('|', array_merge($this->standardPrefixes, $this->customPrefixes)),
                        implode('|', $this->models),
                    ]);
            })->get();

            foreach ($this->models as $model) {
                foreach (array_merge($this->standardPrefixes, $this->customPrefixes) as $prefix) {
                    $permissionName = "{$prefix}_{$model}";
                    $permission = Permission::where('name', $permissionName)->first();
                    if ($permission && ! $superAdmin->hasPermissionTo($permission)) {
                        $superAdmin->givePermissionTo($permission);
                    }
                }
            }

            $this->command->info('✓ super_admin role updated with all permissions');
        }

        $this->command->newLine();
        $this->command->info('✓ Permission seeding completed successfully!');
        $this->command->newLine();
        $this->displayPermissionSummary();
    }

    /**
     * Display a summary of created permissions.
     */
    private function displayPermissionSummary(): void
    {
        $this->command->info('=== Permission Summary ===');
        $this->command->newLine();

        foreach ($this->models as $model) {
            $modelName = str_replace('::', ' > ', $model);
            $this->command->line("Model: {$modelName}");

            $permissions = Permission::where('name', 'like', "%_{$model}")->pluck('name')->toArray();
            sort($permissions);

            foreach ($permissions as $permission) {
                $this->command->line("  • {$permission}");
            }

            $this->command->newLine();
        }

        $totalPermissions = Permission::where(function ($query) {
            foreach ($this->models as $model) {
                $query->orWhere('name', 'like', "%_{$model}");
            }
        })->count();

        $this->command->info("Total permissions for these models: {$totalPermissions}");
        $this->command->newLine();
        $this->command->info('🔑 IMPORTANT: The "change_state" permission is separate from "update"');
        $this->command->info('   This allows granular control over who can change model states/statuses.');
    }
}
