<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_fillable_attributes(): void
    {
        $user = new User();

        $this->assertEquals([
            'name',
            'email',
            'password',
        ], $user->getFillable());
    }

    public function test_user_has_hidden_attributes(): void
    {
        $user = new User();

        $this->assertEquals([
            'password',
            'remember_token',
        ], $user->getHidden());
    }

    public function test_user_can_be_soft_deleted(): void
    {
        $user = User::factory()->create();

        $user->delete();

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    public function test_user_can_be_restored_after_soft_delete(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $user->restore();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }

    public function test_user_can_have_roles(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);

        $user->assignRole($role);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertCount(1, $user->roles);
    }

    public function test_user_can_have_multiple_roles(): void
    {
        $user = User::factory()->create();
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'editor', 'guard_name' => 'web']);

        $user->assignRole(['admin', 'editor']);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('editor'));
        $this->assertCount(2, $user->roles);
    }

    public function test_user_logs_activity_on_update(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
        ]);

        activity()->causedBy($user)->performedOn($user)->log('updated');

        $user->update(['name' => 'Updated Name']);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => User::class,
            'subject_id' => $user->id,
        ]);
    }

    public function test_user_password_is_hashed(): void
    {
        $user = User::factory()->create([
            'password' => 'plain-text-password',
        ]);

        $this->assertNotEquals('plain-text-password', $user->password);
        $this->assertTrue(password_verify('plain-text-password', $user->password));
    }

    public function test_user_can_access_filament_panel(): void
    {
        $user = User::factory()->create();

        // Mock a panel
        $panel = $this->createMock(\Filament\Panel::class);

        $this->assertTrue($user->canAccessPanel($panel));
    }

    public function test_activity_log_options_are_configured(): void
    {
        $user = User::factory()->create();

        $logOptions = $user->getActivitylogOptions();

        $this->assertInstanceOf(\Spatie\Activitylog\LogOptions::class, $logOptions);
    }
}
