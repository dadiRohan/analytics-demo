<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Analytics
{
    public static function track(
        string $name,
        array $properties = [],
        ?int $userId = null,
        ?string $category = null
    ): void {
        $event = [
            'event_id'       => (string) Str::uuid(),
            'event_name'     => $name,
            'event_category' => $category,
            'properties'     => $properties,
            'anonymous_id'   => request()->cookie('ajs_anonymous_id') ?? (string) Str::uuid(),
            'session_id'     => session()->getId() ?: (string) Str::uuid(),
            'user_id'        => $userId ?? auth()->id(),
            'page_url'       => request()->fullUrl(),
            'user_agent'     => request()->userAgent(),
            'ip'             => request()->ip(),
            'occurred_at'    => now()->toIso8601String(),
        ];

        dispatch(function () use ($event) {
            try {
                Http::withToken(config('analytics.ingest_token'))
                    ->timeout(5)
                    ->post(config('analytics.ingest_url'), ['events' => [$event]]);
            } catch (\Throwable $e) {
                Log::warning('Server-side analytics failed', ['error' => $e->getMessage()]);
            }
        })->afterResponse();
    }
}