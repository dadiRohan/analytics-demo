<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnalyticsQueryTest extends TestCase
{
    use RefreshDatabase;

    private function makeEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'event_id'     => (string) Str::uuid(),
            'event_name'   => 'page_view',
            'anonymous_id' => 'anon-1',
            'session_id'   => 'sess-1',
            'occurred_at'  => now()->subDays(2),
            'received_at'  => now(),
            'properties'   => ['path' => '/menu'],
            'page_url'     => 'http://localhost/menu',
        ], $overrides));
    }

    public function test_overview_counts_page_views_and_uniques()
    {
        $this->makeEvent();
        $this->makeEvent(['anonymous_id' => 'anon-2']);
        $this->makeEvent(['session_id'   => 'sess-2']);

        $this->withToken(config('analytics.read_token'))
            ->getJson('/api/analytics/overview?from=' . now()->subDays(5)->toDateString()
                . '&to=' . now()->toDateString())
            ->assertOk()
            ->assertJson([
                'unique_visitors' => 2,
                'sessions'        => 2,
            ]);
    }

    public function test_popular_pages_returns_paths()
    {
        $this->makeEvent(['properties' => ['path' => '/menu']]);
        $this->makeEvent(['properties' => ['path' => '/menu']]);
        $this->makeEvent(['properties' => ['path' => '/order']]);

        $res = $this->withToken(config('analytics.read_token'))
            ->getJson('/api/analytics/popular-pages?from=' . now()->subDays(5)->toDateString());

        $res->assertOk();
        $first = $res->json()[0];
        $this->assertSame('/menu', $first['page']);
        $this->assertSame(2, $first['views']);
    }
}