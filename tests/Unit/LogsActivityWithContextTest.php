<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Traits\LogsActivityWithContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Tests\TestCase;

/**
 * Test model that uses both LogsActivity and LogsActivityWithContext traits.
 */
class TestActivityModel extends Model
{
    use LogsActivity;
    use LogsActivityWithContext;

    protected $table = 'users'; // Reuse users table for testing

    protected $fillable = ['name', 'email', 'password'];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logOnly(['name', 'email'])
            ->logOnlyDirty();
    }
}

class LogsActivityWithContextTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test logging activity by process with data.
     */
    public function test_log_activity_by_process_with_data(): void
    {
        $model = TestActivityModel::create([
            'name' => 'Test Model',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Clear any creation logs
        Activity::truncate();

        $activity = $model->logActivityByProcess(
            'imported from API',
            'DataImportJob',
            ['batch_id' => 123, 'source' => 'external_api'],
        );

        $this->assertInstanceOf(Activity::class, $activity);
        $this->assertEquals('imported from API', $activity->description);
        $this->assertEquals($model->id, $activity->subject_id);
        $this->assertEquals(TestActivityModel::class, $activity->subject_type);
        $this->assertNull($activity->causer_id);
        $this->assertEquals('process', $activity->properties['source']);
        $this->assertEquals('DataImportJob', $activity->properties['process_name']);
        $this->assertEquals(123, $activity->properties['data']['batch_id']);
        $this->assertEquals('external_api', $activity->properties['data']['source']);
    }

    /**
     * Test logging activity by process without data.
     */
    public function test_log_activity_by_process_without_data(): void
    {
        $model = TestActivityModel::create([
            'name' => 'Test Model',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Activity::truncate();

        $activity = $model->logActivityByProcess('synchronized', 'SyncProcess');

        $this->assertEquals('synchronized', $activity->description);
        $this->assertEquals('process', $activity->properties['source']);
        $this->assertEquals('SyncProcess', $activity->properties['process_name']);
        $this->assertArrayNotHasKey('data', $activity->properties->toArray());
    }

    /**
     * Test logging activity by system with data.
     */
    public function test_log_activity_by_system_with_data(): void
    {
        $model = TestActivityModel::create([
            'name' => 'Test Model',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Activity::truncate();

        $activity = $model->logActivityBySystem(
            'auto-archived',
            ['reason' => 'inactivity', 'days' => 90, 'cron_job' => 'archive-inactive'],
        );

        $this->assertEquals('auto-archived', $activity->description);
        $this->assertNull($activity->causer_id);
        $this->assertEquals('system', $activity->properties['source']);
        $this->assertEquals('inactivity', $activity->properties['data']['reason']);
        $this->assertEquals(90, $activity->properties['data']['days']);
        $this->assertEquals('archive-inactive', $activity->properties['data']['cron_job']);
    }

    /**
     * Test logging activity by system without data.
     */
    public function test_log_activity_by_system_without_data(): void
    {
        $model = TestActivityModel::create([
            'name' => 'Test Model',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Activity::truncate();

        $activity = $model->logActivityBySystem('status updated by scheduler');

        $this->assertEquals('status updated by scheduler', $activity->description);
        $this->assertEquals('system', $activity->properties['source']);
        $this->assertArrayNotHasKey('data', $activity->properties->toArray());
    }

    /**
     * Test logging activity by user with data.
     */
    public function test_log_activity_by_user_with_data(): void
    {
        $user = User::factory()->create(['name' => 'Admin User']);
        $model = TestActivityModel::create([
            'name' => 'Test Model',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Activity::truncate();

        $activity = $model->logActivityByUser(
            'updated via admin panel',
            $user,
            ['ip' => '192.168.1.1', 'endpoint' => '/admin/models/123'],
        );

        $this->assertEquals('updated via admin panel', $activity->description);
        $this->assertEquals($user->id, $activity->causer_id);
        $this->assertEquals(User::class, $activity->causer_type);
        $this->assertEquals('user', $activity->properties['source']);
        $this->assertEquals('192.168.1.1', $activity->properties['data']['ip']);
        $this->assertEquals('/admin/models/123', $activity->properties['data']['endpoint']);
    }

    /**
     * Test logging activity by user without data.
     */
    public function test_log_activity_by_user_without_data(): void
    {
        $user = User::factory()->create();
        $model = TestActivityModel::create([
            'name' => 'Test Model',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Activity::truncate();

        $activity = $model->logActivityByUser('manually updated', $user);

        $this->assertEquals('manually updated', $activity->description);
        $this->assertEquals($user->id, $activity->causer_id);
        $this->assertEquals('user', $activity->properties['source']);
        $this->assertArrayNotHasKey('data', $activity->properties->toArray());
    }

    /**
     * Test logging activity with custom properties without causer.
     */
    public function test_log_activity_with_custom_properties_without_causer(): void
    {
        $model = TestActivityModel::create([
            'name' => 'Test Model',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Activity::truncate();

        $activity = $model->logActivityWithProperties(
            'webhook received',
            [
                'webhook_id' => 'wh_12345',
                'provider' => 'stripe',
                'event' => 'payment.succeeded',
                'payload' => ['amount' => 5000, 'currency' => 'usd'],
            ],
        );

        $this->assertEquals('webhook received', $activity->description);
        $this->assertNull($activity->causer_id);
        $this->assertEquals('wh_12345', $activity->properties['webhook_id']);
        $this->assertEquals('stripe', $activity->properties['provider']);
        $this->assertEquals('payment.succeeded', $activity->properties['event']);
        $this->assertIsArray($activity->properties['payload']);
        $this->assertEquals(5000, $activity->properties['payload']['amount']);
    }

    /**
     * Test logging activity with custom properties with causer.
     */
    public function test_log_activity_with_custom_properties_with_causer(): void
    {
        $user = User::factory()->create(['name' => 'Manager']);
        $model = TestActivityModel::create([
            'name' => 'Test Model',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Activity::truncate();

        $activity = $model->logActivityWithProperties(
            'approved',
            ['tier' => 'premium', 'notes' => 'Meets criteria', 'approval_date' => now()->toDateString()],
            $user,
        );

        $this->assertEquals('approved', $activity->description);
        $this->assertEquals($user->id, $activity->causer_id);
        $this->assertEquals(User::class, $activity->causer_type);
        $this->assertEquals('premium', $activity->properties['tier']);
        $this->assertEquals('Meets criteria', $activity->properties['notes']);
        $this->assertNotNull($activity->properties['approval_date']);
    }

    /**
     * Test that all trait methods exist and are callable.
     */
    public function test_all_trait_methods_exist(): void
    {
        $model = new TestActivityModel();

        $this->assertTrue(method_exists($model, 'logActivityByProcess'));
        $this->assertTrue(method_exists($model, 'logActivityBySystem'));
        $this->assertTrue(method_exists($model, 'logActivityByUser'));
        $this->assertTrue(method_exists($model, 'logActivityWithProperties'));
    }

    /**
     * Test automatic logging still works with the trait.
     */
    public function test_automatic_logging_still_works(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Activity::truncate();

        $model = TestActivityModel::create([
            'name' => 'Auto Log Test',
            'email' => 'auto@example.com',
            'password' => bcrypt('password'),
        ]);

        $activities = Activity::where('subject_id', $model->id)
            ->where('subject_type', TestActivityModel::class)
            ->get();

        $this->assertGreaterThan(0, $activities->count());
        $this->assertEquals($user->id, $activities->first()->causer_id);
    }
}
