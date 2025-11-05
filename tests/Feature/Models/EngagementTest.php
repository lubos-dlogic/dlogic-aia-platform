<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Client;
use App\Models\Engagement;
use App\Models\EngagementAudit;
use App\Models\EngagementProcess;
use App\Models\User;
use App\States\EngagementActive;
use App\States\EngagementCancelled;
use App\States\EngagementCompleted;
use App\States\EngagementPlanning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngagementTest extends TestCase
{
    use RefreshDatabase;

    public function it_can_create_an_engagement(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST001',
            'country' => 'US',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $engagement = Engagement::create([
            'key' => 'ENG001',
            'name' => 'Test Engagement',
            'client_fk' => $client->id,
            'version' => 1,
            'description' => 'Test description',
            'data' => ['key1' => 'value1', 'key2' => 'value2'],
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $this->assertDatabaseHas('engagements', [
            'key' => 'ENG001',
            'name' => 'Test Engagement',
        ]);

        $this->assertInstanceOf(Engagement::class, $engagement);
    }

    public function it_has_default_planning_state(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST002',
            'country' => 'UK',
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $engagement = Engagement::create([
            'key' => 'ENG002',
            'name' => 'Planning Engagement',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $this->assertInstanceOf(EngagementPlanning::class, $engagement->state);
        $this->assertEquals('planning', $engagement->state::name());
    }

    public function it_can_transition_from_planning_to_active(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST003',
            'country' => 'CA',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $engagement = Engagement::create([
            'key' => 'ENG003',
            'name' => 'Active Engagement',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $engagement->state->transitionTo(EngagementActive::class);

        $this->assertInstanceOf(EngagementActive::class, $engagement->state);
        $this->assertEquals('active', $engagement->state::name());
    }

    public function it_can_transition_from_active_to_completed(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST004',
            'country' => 'AU',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $engagement = Engagement::create([
            'key' => 'ENG004',
            'name' => 'Completed Engagement',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        // Transition planning -> active -> completed
        $engagement->state->transitionTo(EngagementActive::class);
        $engagement->state->transitionTo(EngagementCompleted::class);

        $this->assertInstanceOf(EngagementCompleted::class, $engagement->state);
        $this->assertEquals('completed', $engagement->state::name());
    }

    public function it_can_transition_to_cancelled(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST005',
            'country' => 'DE',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $engagement = Engagement::create([
            'key' => 'ENG005',
            'name' => 'Cancelled Engagement',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $engagement->state->transitionTo(EngagementCancelled::class);

        $this->assertInstanceOf(EngagementCancelled::class, $engagement->state);
        $this->assertEquals('cancelled', $engagement->state::name());
    }

    public function it_belongs_to_a_client(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST006',
            'country' => 'FR',
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $engagement = Engagement::create([
            'key' => 'ENG006',
            'name' => 'Client Engagement',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $this->assertInstanceOf(Client::class, $engagement->client);
        $this->assertEquals($client->id, $engagement->client->id);
    }

    public function it_belongs_to_a_creator_user(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST007',
            'country' => 'IT',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $engagement = Engagement::create([
            'key' => 'ENG007',
            'name' => 'User Engagement',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $this->assertInstanceOf(User::class, $engagement->creator);
        $this->assertEquals($user->id, $engagement->creator->id);
    }

    public function it_has_many_audits(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST008',
            'country' => 'ES',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $engagement = Engagement::create([
            'key' => 'ENG008',
            'name' => 'Audited Engagement',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $audit1 = EngagementAudit::create([
            'engagement_fk' => $engagement->id,
            'name' => 'Audit 1',
            'type' => 'SINGLE',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $audit2 = EngagementAudit::create([
            'engagement_fk' => $engagement->id,
            'name' => 'Audit 2',
            'type' => 'COMPREHENSIVE',
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $this->assertCount(2, $engagement->audits);
        $this->assertTrue($engagement->audits->contains($audit1));
        $this->assertTrue($engagement->audits->contains($audit2));
    }

    public function it_has_many_processes(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST009',
            'country' => 'NL',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $engagement = Engagement::create([
            'key' => 'ENG009',
            'name' => 'Process Engagement',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $process1 = EngagementProcess::create([
            'engagement_fk' => $engagement->id,
            'key' => 'P001',
            'name' => 'Process 1',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $process2 = EngagementProcess::create([
            'engagement_fk' => $engagement->id,
            'key' => 'P002',
            'name' => 'Process 2',
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $this->assertCount(2, $engagement->processes);
        $this->assertTrue($engagement->processes->contains($process1));
        $this->assertTrue($engagement->processes->contains($process2));
    }

    public function it_casts_data_to_array(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST010',
            'country' => 'SE',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $data = ['metadata' => ['key1' => 'value1', 'key2' => 'value2']];

        $engagement = Engagement::create([
            'key' => 'ENG010',
            'name' => 'Data Engagement',
            'client_fk' => $client->id,
            'data' => $data,
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $this->assertIsArray($engagement->data);
        $this->assertEquals($data, $engagement->data);
    }

    public function it_can_attach_tags(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST011',
            'country' => 'NO',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $engagement = Engagement::create([
            'key' => 'ENG011',
            'name' => 'Tagged Engagement',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $engagement->attachTag('urgent');
        $engagement->attachTag('high-value');

        $this->assertTrue($engagement->tags->pluck('name')->contains('urgent'));
        $this->assertTrue($engagement->tags->pluck('name')->contains('high-value'));
        $this->assertCount(2, $engagement->tags);
    }

    public function it_can_query_engagements_with_specific_tags(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST012',
            'country' => 'DK',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $engagement1 = Engagement::create([
            'key' => 'ENG012',
            'name' => 'Engagement 1',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);
        $engagement1->attachTag('urgent');

        $engagement2 = Engagement::create([
            'key' => 'ENG013',
            'name' => 'Engagement 2',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);
        $engagement2->attachTag('urgent');

        $engagement3 = Engagement::create([
            'key' => 'ENG014',
            'name' => 'Engagement 3',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);
        $engagement3->attachTag('standard');

        $urgentEngagements = Engagement::withAllTags(['urgent'])->get();

        $this->assertCount(2, $urgentEngagements);
        $this->assertTrue($urgentEngagements->contains($engagement1));
        $this->assertTrue($urgentEngagements->contains($engagement2));
        $this->assertFalse($urgentEngagements->contains($engagement3));
    }
}
