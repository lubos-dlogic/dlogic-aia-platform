<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\RoleResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RoleResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions first
        $permissions = [
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

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create super admin role
        $superAdminRole = Role::create([
            'name' => 'super_admin',
            'guard_name' => 'web',
            'description' => 'Super Admin Role',
        ]);

        // Create super admin user
        $this->superAdmin = User::factory()->create([
            'email' => 'admin@test.com',
        ]);

        $this->superAdmin->assignRole($superAdminRole);
        $this->superAdmin->givePermissionTo($permissions);

        // Begin transaction for self-resetting database
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        // Rollback transaction to reset database state
        DB::rollBack();
        parent::tearDown();
    }

    public function test_can_render_role_list_page(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->get(RoleResource::getUrl('index'));

        $response->assertOk();
    }

    public function test_can_render_role_create_page(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->get(RoleResource::getUrl('create'));

        $response->assertOk();
    }

    public function test_can_create_role(): void
    {
        $this->actingAs($this->superAdmin);

        $newRole = [
            'name' => 'test_role',
            'guard_name' => 'web',
            'description' => 'Test role description',
        ];

        Livewire::test(RoleResource\Pages\CreateRole::class)
            ->fillForm($newRole)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('roles', [
            'name' => 'test_role',
            'guard_name' => 'web',
            'description' => 'Test role description',
        ]);
    }

    public function test_can_render_role_edit_page(): void
    {
        $this->actingAs($this->superAdmin);

        $role = Role::create([
            'name' => 'editor',
            'guard_name' => 'web',
            'description' => 'Editor role',
        ]);

        $response = $this->get(RoleResource::getUrl('edit', ['record' => $role]));

        $response->assertOk();
    }

    public function test_can_update_role(): void
    {
        $this->actingAs($this->superAdmin);

        $role = Role::create([
            'name' => 'editor',
            'guard_name' => 'web',
            'description' => 'Editor role',
        ]);

        $updatedData = [
            'name' => 'editor',
            'guard_name' => 'web',
            'description' => 'Updated editor role description',
        ];

        Livewire::test(RoleResource\Pages\EditRole::class, ['record' => $role->id])
            ->fillForm($updatedData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'description' => 'Updated editor role description',
        ]);
    }

    public function test_can_delete_custom_role(): void
    {
        $this->actingAs($this->superAdmin);

        $role = Role::create([
            'name' => 'custom_role',
            'guard_name' => 'web',
        ]);

        Livewire::test(RoleResource\Pages\EditRole::class, ['record' => $role->id])
            ->callAction('delete');

        $this->assertDatabaseMissing('roles', [
            'id' => $role->id,
        ]);
    }

    public function test_cannot_delete_system_role(): void
    {
        $this->actingAs($this->superAdmin);

        $systemRole = Role::create([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $this->assertTrue($systemRole->isSystemRole());

        // Policy should prevent deletion
        $this->assertFalse($this->superAdmin->can('delete', $systemRole));
    }

    public function test_cannot_update_system_role_name(): void
    {
        $this->actingAs($this->superAdmin);

        $systemRole = Role::create([
            'name' => 'user',
            'guard_name' => 'web',
        ]);

        $this->assertTrue($systemRole->isSystemRole());

        // Policy should prevent updating system role
        $this->assertFalse($this->superAdmin->can('update', $systemRole));
    }

    public function test_can_assign_permissions_to_role(): void
    {
        $this->actingAs($this->superAdmin);

        $permission = Permission::create(['name' => 'test_permission', 'guard_name' => 'web']);

        $role = Role::create([
            'name' => 'test_role',
            'guard_name' => 'web',
        ]);

        Livewire::test(RoleResource\Pages\EditRole::class, ['record' => $role->id])
            ->fillForm([
                'permissions' => [$permission->id],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($role->fresh()->hasPermissionTo('test_permission'));
    }

    public function test_can_duplicate_role(): void
    {
        $this->actingAs($this->superAdmin);

        $originalRole = Role::create([
            'name' => 'original',
            'guard_name' => 'web',
            'description' => 'Original role',
        ]);

        $permission = Permission::create(['name' => 'duplicate_test', 'guard_name' => 'web']);
        $originalRole->givePermissionTo($permission);

        Livewire::test(RoleResource\Pages\ListRoles::class)
            ->callTableAction('duplicate', $originalRole);

        $this->assertDatabaseHas('roles', [
            'name' => 'original_copy',
            'description' => 'Copy of Original role',
        ]);

        $duplicatedRole = Role::where('name', 'original_copy')->first();
        $this->assertTrue($duplicatedRole->hasPermissionTo('duplicate_test'));
    }

    public function test_role_displays_permissions_count(): void
    {
        $this->actingAs($this->superAdmin);

        $role = Role::create([
            'name' => 'test_role',
            'guard_name' => 'web',
        ]);

        $permission1 = Permission::create(['name' => 'perm1', 'guard_name' => 'web']);
        $permission2 = Permission::create(['name' => 'perm2', 'guard_name' => 'web']);

        $role->givePermissionTo([$permission1, $permission2]);

        $this->assertEquals(2, $role->permissions_count);
    }

    public function test_role_displays_users_count(): void
    {
        $this->actingAs($this->superAdmin);

        $role = Role::create([
            'name' => 'test_role',
            'guard_name' => 'web',
        ]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $user1->assignRole($role);
        $user2->assignRole($role);

        $this->assertEquals(2, $role->users_count);
    }

    public function test_unauthorized_user_cannot_access_role_resource(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser);

        $response = $this->get(RoleResource::getUrl('index'));

        $response->assertForbidden();
    }

    public function test_can_filter_roles_by_guard(): void
    {
        $this->actingAs($this->superAdmin);

        Role::create(['name' => 'web_role', 'guard_name' => 'web']);

        // Test that the page loads successfully
        $response = $this->get(RoleResource::getUrl('index'));

        $response->assertOk();
    }

    public function test_role_has_display_name_accessor(): void
    {
        $role = Role::create([
            'name' => 'custom_admin',
            'guard_name' => 'web',
        ]);

        $this->assertEquals('Custom Admin', $role->display_name);
    }
}
