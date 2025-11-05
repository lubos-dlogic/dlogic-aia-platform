<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ShieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for User resource
        $userPermissions = [
            'view_any_user',
            'view_user',
            'create_user',
            'update_user',
            'delete_user',
            'delete_any_user',
            'restore_user',
            'restore_any_user',
            'force_delete_user',
            'force_delete_any_user',
            'replicate_user',
            'reorder_user',
        ];

        foreach ($userPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create permissions for Role resource
        $rolePermissions = [
            'view_any_role',
            'view_role',
            'create_role',
            'update_role',
            'delete_role',
            'delete_any_role',
            'restore_role',
            'restore_any_role',
            'force_delete_role',
            'force_delete_any_role',
            'replicate_role',
            'reorder_role',
        ];

        foreach ($rolePermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles with descriptions
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'web'],
            ['description' => 'Full system access with all permissions. Can manage all users, roles, and settings.'],
        );

        $adminRole = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['description' => 'Administrative access with limited permissions. Can manage users but not roles or system settings.'],
        );

        $userRole = Role::firstOrCreate(
            ['name' => 'user', 'guard_name' => 'web'],
            ['description' => 'Basic user access. Can view their own profile and basic information.'],
        );

        // Assign all permissions to super_admin
        $superAdminRole->givePermissionTo(Permission::all());

        // Assign limited permissions to admin
        $adminRole->givePermissionTo([
            'view_any_user',
            'view_user',
            'create_user',
            'update_user',
        ]);

        // User role gets no special permissions by default
        $userRole->givePermissionTo([
            'view_user',
        ]);

        // Create super admin user
        $superAdmin = User::firstOrCreate(
            ['email' => env('SUPERADMIN_USER_EMAIL', 'admin@dlogic.com')],
            [
                'name' => env('SUPERADMIN_USER_FULLNAME', 'Super Admin'),
                'password' => Hash::make(env('SUPERADMIN_USER_PASSWORD', 'password#951')),
                'email_verified_at' => now(),
            ],
        );

        $superAdmin->assignRole($superAdminRole);

        $this->command->info('Shield seeder completed successfully!');
        $this->command->info('Super Admin Login:');
        $this->command->info('Email: admin@dlogic.com');
        $this->command->info('Password: password');
    }
}
