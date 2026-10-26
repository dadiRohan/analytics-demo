<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EventIngestionTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        return [
            'events' => [[
                'event_id'     => (string) Str::uuid(),
                'event_name'   => 'page_view',
                'properties'   => ['path' => '/menu'],
                'anonymous_id' => (string) Str::uuid(),
                'session_id'   => (string) Str::uuid(),
                'page_url'     => 'http://localhost/menu',
                'occurred_at'  => now()->toIso8601String(),
            ]],
        ];
    }

    public function test_rejects_missing_token()
    {
        $this->postJson('/api/events', $this->payload())->assertStatus(401);
    }

    public function test_accepts_valid_event()
    {
        $this->withToken(config('analytics.ingest_token'))
            ->postJson('/api/events', $this->payload())
            ->assertStatus(202)
            ->assertJson(['accepted' => 1]);

        $this->assertDatabaseCount('events', 1);
    }

    public function test_deduplicates_by_event_id()
    {
        $payload = $this->payload();

        $this->withToken(config('analytics.ingest_token'))
            ->postJson('/api/events', $payload)->assertStatus(202);

        $this->withToken(config('analytics.ingest_token'))
            ->postJson('/api/events', $payload)
            ->assertStatus(202)
            ->assertJson(['accepted' => 0]);

        $this->assertDatabaseCount('events', 1);
    }

    public function test_rejects_invalid_event()
    {
        $bad = $this->payload();
        $bad['events'][0]['event_name'] = '';

        $this->withToken(config('analytics.ingest_token'))
            ->postJson('/api/events', $bad)
            ->assertStatus(422);
    }
}