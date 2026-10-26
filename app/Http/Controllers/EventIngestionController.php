<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\IngestEventsRequest;
use App\Jobs\UpdateDailyAggregates;
use App\Models\Event;
use Illuminate\Support\Facades\Log;

class EventIngestionController extends Controller
{
    public function ingest(IngestEventsRequest $request)
    {
        $now = now();

        $rows = collect($request->validated()['events'])->map(function ($e) use ($now, $request) {
            return [
                'event_id'       => $e['event_id'],
                'event_name'     => $e['event_name'],
                'event_category' => $e['event_category'] ?? null,
                'properties'     => isset($e['properties']) ? json_encode($e['properties']) : null,
                'anonymous_id'   => $e['anonymous_id'],
                'session_id'     => $e['session_id'],
                'user_id'        => $e['user_id'] ?? null,
                'page_url'       => $e['page_url'] ?? null,
                'user_agent'     => $e['user_agent'] ?? $request->userAgent(),
                'ip'             => $e['ip'] ?? $request->ip(),
                'occurred_at'    => $e['occurred_at'],
                'received_at'    => $now,
            ];
        })->all();

        try {
            $inserted = Event::insertOrIgnore($rows);
        } catch (\Throwable $ex) {
            Log::error('Event ingestion failed', ['error' => $ex->getMessage()]);
            return response()->json(['error' => 'Ingestion failed'], 500);
        }

        if ($inserted > 0) {
            UpdateDailyAggregates::dispatch();
        }

        return response()->json(['accepted' => $inserted], 202);
    }
}