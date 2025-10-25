<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Client;
use App\Models\Engagement;
use App\Models\EngagementProcess;
use App\Models\EngagementProcessVersion;
use App\Models\User;
use App\States\EngagementProcessVersionActive;
use App\States\EngagementProcessVersionCancelled;
use App\States\EngagementProcessVersionDraft;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngagementProcessVersionTest extends TestCase
{
    use RefreshDatabase;

    public function it_can_create_an_engagement_process_version(): void
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
            'name' => 'Test Process',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $version = EngagementProcessVersion::create([
            'process_fk' => $process->id,
            'name' => 'Version 1.0',
            'version_number' => 1,
            'description' => 'Initial version',
            'data' => ['changes' => ['initial release'], 'author' => 'John Doe'],
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $this->assertDatabaseHas('engagement_processes_versions', [
            'name' => 'Version 1.0',
            'version_number' => 1,
        ]);

        $this->assertInstanceOf(EngagementProcessVersion::class, $version);
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

        $process = EngagementProcess::create([
            'engagement_fk' => $engagement->id,
            'key' => 'PROC',
            'name' => 'Test Process',
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $version = EngagementProcessVersion::create([
            'process_fk' => $process->id,
            'name' => 'Draft Version',
            'version_number' => 1,
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $this->assertInstanceOf(EngagementProcessVersionDraft::class, $version->state);
        $this->assertEquals('draft', $version->state::name());
    }

    public function it_can_transition_from_draft_to_active(): void
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
            'key' => 'PROC',
            'name' => 'Test Process',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $version = EngagementProcessVersion::create([
            'process_fk' => $process->id,
            'name' => 'Active Version',
            'version_number' => 1,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $version->state->transitionTo(EngagementProcessVersionActive::class);

        $this->assertInstanceOf(EngagementProcessVersionActive::class, $version->state);
        $this->assertEquals('active', $version->state::name());
    }

    public function it_can_transition_from_active_to_cancelled(): void
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
            'key' => 'PROC',
            'name' => 'Test Process',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $version = EngagementProcessVersion::create([
            'process_fk' => $process->id,
            'name' => 'Cancelled Version',
            'version_number' => 1,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        // Transition draft -> active -> cancelled
        $version->state->transitionTo(EngagementProcessVersionActive::class);
        $version->state->transitionTo(EngagementProcessVersionCancelled::class);

        $this->assertInstanceOf(EngagementProcessVersionCancelled::class, $version->state);
        $this->assertEquals('cancelled', $version->state::name());
    }

    public function it_belongs_to_an_engagement_process(): void
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
            'key' => 'PROC',
            'name' => 'Test Process',
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $version = EngagementProcessVersion::create([
            'process_fk' => $process->id,
            'name' => 'Process Version',
            'version_number' => 1,
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $this->assertInstanceOf(EngagementProcess::class, $version->process);
        $this->assertEquals($process->id, $version->process->id);
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

        $process = EngagementProcess::create([
            'engagement_fk' => $engagement->id,
            'key' => 'PROC',
            'name' => 'Test Process',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $version = EngagementProcessVersion::create([
            'process_fk' => $process->id,
            'name' => 'User Version',
            'version_number' => 1,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $this->assertInstanceOf(User::class, $version->creator);
        $this->assertEquals($user->id, $version->creator->id);
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

        $process = EngagementProcess::create([
            'engagement_fk' => $engagement->id,
            'key' => 'PROC',
            'name' => 'Test Process',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $data = ['release_notes' => 'Bug fixes', 'breaking_changes' => false];

        $version = EngagementProcessVersion::create([
            'process_fk' => $process->id,
            'name' => 'Data Version',
            'version_number' => 1,
            'data' => $data,
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $this->assertIsArray($version->data);
        $this->assertEquals($data, $version->data);
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

        $process = EngagementProcess::create([
            'engagement_fk' => $engagement->id,
            'key' => 'PROC',
            'name' => 'Test Process',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $version = EngagementProcessVersion::create([
            'process_fk' => $process->id,
            'name' => 'Tagged Version',
            'version_number' => 1,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $version->attachTag('stable');
        $version->attachTag('production');

        $this->assertTrue($version->tags->pluck('name')->contains('stable'));
        $this->assertTrue($version->tags->pluck('name')->contains('production'));
        $this->assertCount(2, $version->tags);
    }

    public function it_enforces_composite_unique_version_constraint(): void
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
            'key' => 'PROC',
            'name' => 'Test Process',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        EngagementProcessVersion::create([
            'process_fk' => $process->id,
            'name' => 'Version 1',
            'version_number' => 1,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        EngagementProcessVersion::create([
            'process_fk' => $process->id,
            'name' => 'Duplicate Version 1',
            'version_number' => 1,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);
    }

    public function it_can_have_multiple_versions_for_same_process(): void
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
            'key' => 'PROC',
            'name' => 'Test Process',
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
            'created_by_process' => 'manual',
        ]);

        $version3 = EngagementProcessVersion::create([
            'process_fk' => $process->id,
            'name' => 'Version 3',
            'version_number' => 3,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $versions = EngagementProcessVersion::where('process_fk', $process->id)->get();

        $this->assertCount(3, $versions);
        $this->assertTrue($versions->contains($version1));
        $this->assertTrue($versions->contains($version2));
        $this->assertTrue($versions->contains($version3));
    }
}
