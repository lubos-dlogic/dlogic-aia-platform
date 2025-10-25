<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Client;
use App\Models\Engagement;
use App\Models\User;
use App\States\ClientActive;
use App\States\ClientArchived;
use App\States\ClientDraft;
use App\States\ClientInactive;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    public function it_can_create_a_client(): void
    {
        $user = User::factory()->create();

        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST001',
            'country' => 'US',
            'website' => 'https://testclient.com',
            'company_gid' => 'GID123456',
            'company_vat_gid' => 'VAT789012',
            'description' => 'A test client',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $this->assertDatabaseHas('clients', [
            'name' => 'Test Client',
            'clinet_key' => 'TEST001',
        ]);

        $this->assertInstanceOf(Client::class, $client);
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

        $this->assertInstanceOf(ClientDraft::class, $client->state);
        $this->assertEquals('draft', $client->state::name());
    }

    public function it_can_transition_from_draft_to_active(): void
    {
        $user = User::factory()->create();

        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST003',
            'country' => 'CA',
            'created_by_user' => $user->id,
            'created_by_process' => 'import',
        ]);

        $client->state->transitionTo(ClientActive::class);

        $this->assertInstanceOf(ClientActive::class, $client->state);
        $this->assertEquals('active', $client->state::name());
    }

    public function it_can_transition_from_active_to_inactive(): void
    {
        $user = User::factory()->create();

        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST004',
            'country' => 'AU',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        // Transition draft -> active -> inactive
        $client->state->transitionTo(ClientActive::class);
        $client->state->transitionTo(ClientInactive::class);

        $this->assertInstanceOf(ClientInactive::class, $client->state);
        $this->assertEquals('inactive', $client->state::name());
    }

    public function it_can_transition_from_active_to_archived(): void
    {
        $user = User::factory()->create();

        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST005',
            'country' => 'DE',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        // Transition draft -> active -> archived
        $client->state->transitionTo(ClientActive::class);
        $client->state->transitionTo(ClientArchived::class);

        $this->assertInstanceOf(ClientArchived::class, $client->state);
        $this->assertEquals('archived', $client->state::name());
    }

    public function it_belongs_to_a_creator_user(): void
    {
        $user = User::factory()->create();

        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST006',
            'country' => 'FR',
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $this->assertInstanceOf(User::class, $client->creator);
        $this->assertEquals($user->id, $client->creator->id);
    }

    public function it_has_many_engagements(): void
    {
        $user = User::factory()->create();

        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST007',
            'country' => 'IT',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $engagement1 = Engagement::create([
            'key' => 'ENG001',
            'name' => 'Engagement 1',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $engagement2 = Engagement::create([
            'key' => 'ENG002',
            'name' => 'Engagement 2',
            'client_fk' => $client->id,
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $this->assertCount(2, $client->engagements);
        $this->assertTrue($client->engagements->contains($engagement1));
        $this->assertTrue($client->engagements->contains($engagement2));
    }

    public function it_can_attach_tags(): void
    {
        $user = User::factory()->create();

        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST008',
            'country' => 'ES',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $client->attachTag('enterprise');
        $client->attachTag('high-priority');

        $this->assertTrue($client->tags->pluck('name')->contains('enterprise'));
        $this->assertTrue($client->tags->pluck('name')->contains('high-priority'));
        $this->assertCount(2, $client->tags);
    }

    public function it_can_attach_multiple_tags_at_once(): void
    {
        $user = User::factory()->create();

        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST009',
            'country' => 'NL',
            'created_by_user' => $user->id,
            'created_by_process' => 'import',
        ]);

        $client->attachTags(['vip', 'financial-sector', 'regulated']);

        $this->assertCount(3, $client->tags);
        $this->assertTrue($client->hasTag('vip'));
        $this->assertTrue($client->hasTag('financial-sector'));
        $this->assertTrue($client->hasTag('regulated'));
    }

    public function it_can_detach_tags(): void
    {
        $user = User::factory()->create();

        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST010',
            'country' => 'SE',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);

        $client->attachTags(['tag1', 'tag2', 'tag3']);
        $this->assertCount(3, $client->tags);

        $client->detachTag('tag2');
        $client->refresh();
        $this->assertCount(2, $client->tags);
        $this->assertFalse($client->hasTag('tag2'));
    }

    public function it_can_sync_tags(): void
    {
        $user = User::factory()->create();

        $client = Client::create([
            'name' => 'Test Client',
            'clinet_key' => 'TEST011',
            'country' => 'NO',
            'created_by_user' => $user->id,
            'created_by_process' => 'api',
        ]);

        $client->attachTags(['old-tag1', 'old-tag2']);
        $this->assertCount(2, $client->tags);

        $client->syncTags(['new-tag1', 'new-tag2', 'new-tag3']);
        $client->refresh();
        $this->assertCount(3, $client->tags);
        $this->assertFalse($client->hasTag('old-tag1'));
        $this->assertTrue($client->hasTag('new-tag1'));
    }

    public function it_can_query_clients_with_specific_tags(): void
    {
        $user = User::factory()->create();

        $client1 = Client::create([
            'name' => 'Client 1',
            'clinet_key' => 'TEST012',
            'country' => 'DK',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);
        $client1->attachTag('enterprise');

        $client2 = Client::create([
            'name' => 'Client 2',
            'clinet_key' => 'TEST013',
            'country' => 'FI',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);
        $client2->attachTag('enterprise');

        $client3 = Client::create([
            'name' => 'Client 3',
            'clinet_key' => 'TEST014',
            'country' => 'BE',
            'created_by_user' => $user->id,
            'created_by_process' => 'manual',
        ]);
        $client3->attachTag('small-business');

        $enterpriseClients = Client::withAllTags(['enterprise'])->get();

        $this->assertCount(2, $enterpriseClients);
        $this->assertTrue($enterpriseClients->contains($client1));
        $this->assertTrue($enterpriseClients->contains($client2));
        $this->assertFalse($enterpriseClients->contains($client3));
    }
}
