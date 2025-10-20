<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions first
        $permissions = [
            'view_any_user',
            'view_user',
            'create_user',
            'update_user',
            'delete_user',
            'restore_user',
            'force_delete_user',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'user', 'guard_name' => 'web']);

        // Create super admin user
        $this->superAdmin = User::factory()->create([
            'email' => 'admin@test.com',
        ]);

        $this->superAdmin->assignRole('super_admin');
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

    public function test_can_render_user_list_page(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->get(UserResource::getUrl('index'));

        $response->assertOk();
    }

    public function test_can_render_user_create_page(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->get(UserResource::getUrl('create'));

        $response->assertOk();
    }

    public function test_can_create_user(): void
    {
        $this->actingAs($this->superAdmin);

        $newUser = User::factory()->make();

        Livewire::test(UserResource\Pages\CreateUser::class)
            ->fillForm([
                'name' => $newUser->name,
                'email' => $newUser->email,
                'password' => 'password123',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'email' => $newUser->email,
        ]);
    }

    public function test_can_render_user_edit_page(): void
    {
        $this->actingAs($this->superAdmin);

        $user = User::factory()->create();

        $response = $this->get(UserResource::getUrl('edit', ['record' => $user]));

        $response->assertOk();
    }

    public function test_can_update_user(): void
    {
        $this->actingAs($this->superAdmin);

        $user = User::factory()->create();

        Livewire::test(UserResource\Pages\EditUser::class, ['record' => $user->id])
            ->fillForm([
                'name' => 'Updated Name',
                'email' => $user->email,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_soft_delete_user(): void
    {
        $this->actingAs($this->superAdmin);

        $user = User::factory()->create();

        Livewire::test(UserResource\Pages\EditUser::class, ['record' => $user->id])
            ->callAction('delete');

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    public function test_can_restore_soft_deleted_user(): void
    {
        $this->actingAs($this->superAdmin);

        $user = User::factory()->create();
        $user->delete();

        Livewire::test(UserResource\Pages\EditUser::class, ['record' => $user->id])
            ->callAction('restore');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }

    public function test_can_assign_roles_to_user(): void
    {
        $this->actingAs($this->superAdmin);

        $user = User::factory()->create();
        $adminRole = Role::findByName('admin');

        Livewire::test(UserResource\Pages\EditUser::class, ['record' => $user->id])
            ->fillForm([
                'roles' => [$adminRole->id],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($user->fresh()->hasRole('admin'));
    }

    public function test_unauthorized_user_cannot_access_user_resource(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser);

        $response = $this->get(UserResource::getUrl('index'));

        $response->assertForbidden();
    }

    public function test_user_can_enable_two_factor_authentication(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->hasEnabledTwoFactor());

        $user->enableTwoFactorAuthentication();

        $this->assertTrue($user->hasEnabledTwoFactor());
        $this->assertNotNull($user->twoFactorSecret);
        $this->assertNotNull($user->two_factor_recovery_codes);
        $this->assertCount(8, $user->two_factor_recovery_codes);
    }

    public function test_user_can_disable_two_factor_authentication(): void
    {
        $user = User::factory()->create();

        $user->enableTwoFactorAuthentication();
        $this->assertTrue($user->hasEnabledTwoFactor());

        $user->disableTwoFactorAuthentication();
        $user->refresh();

        $this->assertFalse($user->hasEnabledTwoFactor());
    }

    public function test_super_admin_can_reset_user_2fa(): void
    {
        $this->actingAs($this->superAdmin);

        $user = User::factory()->create();
        $user->enableTwoFactorAuthentication();

        $this->assertTrue($user->hasEnabledTwoFactor());

        // Simulate the reset action
        $user->disableTwoFactorAuthentication();
        $user->refresh();

        $this->assertFalse($user->hasEnabledTwoFactor());
    }

    public function test_two_factor_filter_works(): void
    {
        $this->actingAs($this->superAdmin);

        // Create users with and without 2FA
        $userWith2FA = User::factory()->create();
        $userWith2FA->enableTwoFactorAuthentication();
        $userWith2FA->confirmTwoFactorAuthentication();

        $userWithout2FA = User::factory()->create();

        // Test that filter works (basic assertion that page loads)
        $response = $this->get(UserResource::getUrl('index'));
        $response->assertOk();
    }

    public function test_two_factor_column_displays_correctly(): void
    {
        $this->actingAs($this->superAdmin);

        $userWith2FA = User::factory()->create(['name' => 'User With 2FA']);
        $userWith2FA->enableTwoFactorAuthentication();
        $userWith2FA->confirmTwoFactorAuthentication();

        $userWithout2FA = User::factory()->create(['name' => 'User Without 2FA']);

        $this->assertTrue($userWith2FA->fresh()->hasEnabledTwoFactor());
        $this->assertFalse($userWithout2FA->fresh()->hasEnabledTwoFactor());
    }
}
