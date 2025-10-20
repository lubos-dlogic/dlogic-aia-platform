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
        $permissions = [
            'view_any_user',
            'view_user',
            'create_user',
            'update_user',
            'delete_user',
            'restore_user',
            'force_delete_user',
            'replicate_user',
            'reorder_user',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

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
            ['email' => 'admin@dlogic.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
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
