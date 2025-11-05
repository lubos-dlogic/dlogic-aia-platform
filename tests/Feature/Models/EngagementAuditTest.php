<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Client;
use App\Models\Engagement;
use App\Models\EngagementAudit;
use App\Models\User;
use App\States\EngagementAuditCancelled;
use App\States\EngagementAuditCompleted;
use App\States\EngagementAuditDraft;
use App\States\EngagementAuditInProgress;
use App\States\EngagementAuditScheduled;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngagementAuditTest extends TestCase
{
    use RefreshDatabase;

    public function it_can_create_an_engagement_audit(): void
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
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $audit = EngagementAudit::create([
            'engagement_fk' => $engagement->id,
            'name' => 'Q1 2025 Audit',
            'type' => 'COMPREHENSIVE',
            'data' => ['scope' => 'full', 'duration' => '3 months'],
            'description' => 'Comprehensive audit for Q1 2025',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $this->assertDatabaseHas('engagement_audits', [
            'name' => 'Q1 2025 Audit',
            'type' => 'COMPREHENSIVE',
        ]);

        $this->assertInstanceOf(EngagementAudit::class, $audit);
    }

    public function it_has_default_draft_state(): void
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
            'name' => 'Test Engagement',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $audit = EngagementAudit::create([
            'engagement_fk' => $engagement->id,
            'name' => 'Draft Audit',
            'type' => 'SINGLE',
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $this->assertInstanceOf(EngagementAuditDraft::class, $audit->state);
        $this->assertEquals('draft', $audit->state::name());
    }

    public function it_can_transition_from_draft_to_scheduled_to_in_progress(): void
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
            'name' => 'Test Engagement',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $audit = EngagementAudit::create([
            'engagement_fk' => $engagement->id,
            'name' => 'In Progress Audit',
            'type' => 'ENTERPRISE',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        // Transition draft -> scheduled -> in_progress
        $audit->state->transitionTo(EngagementAuditScheduled::class);
        $audit->state->transitionTo(EngagementAuditInProgress::class);

        $this->assertInstanceOf(EngagementAuditInProgress::class, $audit->state);
        $this->assertEquals('in_progress', $audit->state::name());
    }

    public function it_can_transition_from_in_progress_to_completed(): void
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
            'name' => 'Test Engagement',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $audit = EngagementAudit::create([
            'engagement_fk' => $engagement->id,
            'name' => 'Completed Audit',
            'type' => 'SOFT1',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        // Transition draft -> in_progress -> completed
        $audit->state->transitionTo(EngagementAuditInProgress::class);
        $audit->state->transitionTo(EngagementAuditCompleted::class);

        $this->assertInstanceOf(EngagementAuditCompleted::class, $audit->state);
        $this->assertEquals('completed', $audit->state::name());
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
            'name' => 'Test Engagement',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $audit = EngagementAudit::create([
            'engagement_fk' => $engagement->id,
            'name' => 'Cancelled Audit',
            'type' => 'SOFT2',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $audit->state->transitionTo(EngagementAuditCancelled::class);

        $this->assertInstanceOf(EngagementAuditCancelled::class, $audit->state);
        $this->assertEquals('cancelled', $audit->state::name());
    }

    public function it_belongs_to_an_engagement(): void
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
            'name' => 'Test Engagement',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $audit = EngagementAudit::create([
            'engagement_fk' => $engagement->id,
            'name' => 'Engagement Audit',
            'type' => 'SOFT3',
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $this->assertInstanceOf(Engagement::class, $audit->engagement);
        $this->assertEquals($engagement->id, $audit->engagement->id);
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
            'name' => 'Test Engagement',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $audit = EngagementAudit::create([
            'engagement_fk' => $engagement->id,
            'name' => 'User Audit',
            'type' => 'COMPREHENSIVE',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $this->assertInstanceOf(User::class, $audit->creator);
        $this->assertEquals($user->id, $audit->creator->id);
    }

    public function it_casts_data_to_array(): void
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
            'name' => 'Test Engagement',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $data = ['findings' => ['issue1', 'issue2'], 'score' => 85];

        $audit = EngagementAudit::create([
            'engagement_fk' => $engagement->id,
            'name' => 'Data Audit',
            'type' => 'SINGLE',
            'data' => $data,
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $this->assertIsArray($audit->data);
        $this->assertEquals($data, $audit->data);
    }

    public function it_can_attach_tags(): void
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
            'name' => 'Test Engagement',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $audit = EngagementAudit::create([
            'engagement_fk' => $engagement->id,
            'name' => 'Tagged Audit',
            'type' => 'ENTERPRISE',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $audit->attachTag('critical');
        $audit->attachTag('compliance');

        $this->assertTrue($audit->tags->pluck('name')->contains('critical'));
        $this->assertTrue($audit->tags->pluck('name')->contains('compliance'));
        $this->assertCount(2, $audit->tags);
    }

    public function it_supports_all_audit_types(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST010',
            'country' => 'SE',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $engagement = Engagement::create([
            'key' => 'ENG010',
            'name' => 'Test Engagement',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $types = ['SINGLE', 'COMPREHENSIVE', 'ENTERPRISE', 'SOFT1', 'SOFT2', 'SOFT3'];

        foreach ($types as $index => $type) {
            $audit = EngagementAudit::create([
                'engagement_fk' => $engagement->id,
                'name' => "Audit Type {$type}",
                'type' => $type,
                'created_by_user' => $user->id,
                'created_by_process' => 'manual',
            ]);

            $this->assertEquals($type, $audit->type);
        }

        $this->assertCount(6, EngagementAudit::all());
    }
}
