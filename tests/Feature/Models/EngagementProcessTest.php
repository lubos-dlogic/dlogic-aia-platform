<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Client;
use App\Models\Engagement;
use App\Models\EngagementProcess;
use App\Models\EngagementProcessVersion;
use App\Models\User;
use App\States\EngagementProcessAnalysed;
use App\States\EngagementProcessAnalysedAndMapped;
use App\States\EngagementProcessBeingAnalysed;
use App\States\EngagementProcessBeingDescribed;
use App\States\EngagementProcessCancelled;
use App\States\EngagementProcessIdentified;
use App\States\EngagementProcessReadyToAnalyse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngagementProcessTest extends TestCase
{
    use RefreshDatabase;

    public function it_can_create_an_engagement_process(): void
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

        $process = EngagementProcess::create([
            'engagement_fk' => $engagement->id,
            'key' => 'PROC',
            'name' => 'Client Onboarding Process',
            'description' => 'Process for onboarding new clients',
            'data' => ['steps' => 5, 'estimated_duration' => '2 weeks'],
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $this->assertDatabaseHas('engagement_processes', [
            'key' => 'PROC',
            'name' => 'Client Onboarding Process',
        ]);

        $this->assertInstanceOf(EngagementProcess::class, $process);
    }

    public function it_has_default_identified_state(): void
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

        $process = EngagementProcess::create([
            'engagement_fk' => $engagement->id,
            'key' => 'IDEN',
            'name' => 'Identified Process',
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $this->assertInstanceOf(EngagementProcessIdentified::class, $process->state);
        $this->assertEquals('identified', $process->state::name());
    }

    public function it_can_transition_from_identified_to_being_described(): void
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

        $process = EngagementProcess::create([
            'engagement_fk' => $engagement->id,
            'key' => 'DESC',
            'name' => 'Being Described Process',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $process->state->transitionTo(EngagementProcessBeingDescribed::class);

        $this->assertInstanceOf(EngagementProcessBeingDescribed::class, $process->state);
        $this->assertEquals('being_described', $process->state::name());
    }

    public function it_can_transition_from_being_described_to_ready_to_analyse(): void
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

        $process = EngagementProcess::create([
            'engagement_fk' => $engagement->id,
            'key' => 'RDY',
            'name' => 'Ready Process',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        // Transition identified -> being_described -> ready_to_analyse
        $process->state->transitionTo(EngagementProcessBeingDescribed::class);
        $process->state->transitionTo(EngagementProcessReadyToAnalyse::class);

        $this->assertInstanceOf(EngagementProcessReadyToAnalyse::class, $process->state);
        $this->assertEquals('ready_to_analyse', $process->state::name());
    }

    public function it_can_transition_from_ready_to_analyse_to_being_analysed(): void
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

        $process = EngagementProcess::create([
            'engagement_fk' => $engagement->id,
            'key' => 'ANLZ',
            'name' => 'Being Analysed Process',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        // Transition identified -> being_described -> ready_to_analyse -> being_analysed
        $process->state->transitionTo(EngagementProcessBeingDescribed::class);
        $process->state->transitionTo(EngagementProcessReadyToAnalyse::class);
        $process->state->transitionTo(EngagementProcessBeingAnalysed::class);

        $this->assertInstanceOf(EngagementProcessBeingAnalysed::class, $process->state);
        $this->assertEquals('being_analysed', $process->state::name());
    }

    public function it_can_transition_from_being_analysed_to_analysed(): void
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

        $process = EngagementProcess::create([
            'engagement_fk' => $engagement->id,
            'key' => 'ANLD',
            'name' => 'Analysed Process',
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        // Transition through full workflow to analysed
        $process->state->transitionTo(EngagementProcessBeingDescribed::class);
        $process->state->transitionTo(EngagementProcessReadyToAnalyse::class);
        $process->state->transitionTo(EngagementProcessBeingAnalysed::class);
        $process->state->transitionTo(EngagementProcessAnalysed::class);

        $this->assertInstanceOf(EngagementProcessAnalysed::class, $process->state);
        $this->assertEquals('analysed', $process->state::name());
    }

    public function it_can_transition_from_analysed_to_analysed_and_mapped(): void
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

        $process = EngagementProcess::create([
            'engagement_fk' => $engagement->id,
            'key' => 'MPNG',
            'name' => 'Mapped Process',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        // Complete workflow to final state
        $process->state->transitionTo(EngagementProcessBeingDescribed::class);
        $process->state->transitionTo(EngagementProcessReadyToAnalyse::class);
        $process->state->transitionTo(EngagementProcessBeingAnalysed::class);
        $process->state->transitionTo(EngagementProcessAnalysed::class);
        $process->state->transitionTo(EngagementProcessAnalysedAndMapped::class);

        $this->assertInstanceOf(EngagementProcessAnalysedAndMapped::class, $process->state);
        $this->assertEquals('analysed_and_mapped', $process->state::name());
    }

    public function it_can_transition_to_cancelled_from_any_state(): void
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

        $process = EngagementProcess::create([
            'engagement_fk' => $engagement->id,
            'key' => 'CNCL',
            'name' => 'Cancelled Process',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $process->state->transitionTo(EngagementProcessCancelled::class);

        $this->assertInstanceOf(EngagementProcessCancelled::class, $process->state);
        $this->assertEquals('cancelled', $process->state::name());
    }

    public function it_belongs_to_an_engagement(): void
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

        $process = EngagementProcess::create([
            'engagement_fk' => $engagement->id,
            'key' => 'ENGP',
            'name' => 'Engagement Process',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $this->assertInstanceOf(Engagement::class, $process->engagement);
        $this->assertEquals($engagement->id, $process->engagement->id);
    }

    public function it_belongs_to_a_creator_user(): void
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

        $process = EngagementProcess::create([
            'engagement_fk' => $engagement->id,
            'key' => 'USR',
            'name' => 'User Process',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $this->assertInstanceOf(User::class, $process->creator);
        $this->assertEquals($user->id, $process->creator->id);
    }

    public function it_has_many_versions(): void
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
            'name' => 'Test Engagement',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $process = EngagementProcess::create([
            'engagement_fk' => $engagement->id,
            'key' => 'VER',
            'name' => 'Versioned Process',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $version1 = EngagementProcessVersion::create([
            'process_fk' => $process->id,
            'name' => 'Version 1',
            'version_number' => 1,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $version2 = EngagementProcessVersion::create([
            'process_fk' => $process->id,
            'name' => 'Version 2',
            'version_number' => 2,
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $this->assertCount(2, $process->versions);
        $this->assertTrue($process->versions->contains($version1));
        $this->assertTrue($process->versions->contains($version2));
    }

    public function it_casts_data_to_array(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST012',
            'country' => 'DK',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $engagement = Engagement::create([
            'key' => 'ENG012',
            'name' => 'Test Engagement',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $data = ['complexity' => 'high', 'participants' => ['user1', 'user2']];

        $process = EngagementProcess::create([
            'engagement_fk' => $engagement->id,
            'key' => 'DATA',
            'name' => 'Data Process',
            'data' => $data,
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $this->assertIsArray($process->data);
        $this->assertEquals($data, $process->data);
    }

    public function it_can_attach_tags(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST013',
            'country' => 'FI',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $engagement = Engagement::create([
            'key' => 'ENG013',
            'name' => 'Test Engagement',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $process = EngagementProcess::create([
            'engagement_fk' => $engagement->id,
            'key' => 'TAG',
            'name' => 'Tagged Process',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $process->attachTag('ai-adoption');
        $process->attachTag('automation');

        $this->assertTrue($process->tags->pluck('name')->contains('ai-adoption'));
        $this->assertTrue($process->tags->pluck('name')->contains('automation'));
        $this->assertCount(2, $process->tags);
    }

    public function it_enforces_composite_unique_key_constraint(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST014',
            'country' => 'BE',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $engagement = Engagement::create([
            'key' => 'ENG014',
            'name' => 'Test Engagement',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        EngagementProcess::create([
            'engagement_fk' => $engagement->id,
            'key' => 'UNIQ',
            'name' => 'First Process',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        EngagementProcess::create([
            'engagement_fk' => $engagement->id,
            'key' => 'UNIQ',
            'name' => 'Duplicate Process',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);
    }
}
