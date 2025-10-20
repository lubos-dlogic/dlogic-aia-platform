<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'user', 'guard_name' => 'web']);

        // Create super admin user
        $this->superAdmin = User::factory()->create([
            'email' => 'admin@test.com',
        ]);

        $this->superAdmin->assignRole('super_admin');
        $this->superAdmin->givePermissionTo([
            'view_any_user',
            'view_user',
            'create_user',
            'update_user',
            'delete_user',
            'restore_user',
            'force_delete_user',
        ]);
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
}