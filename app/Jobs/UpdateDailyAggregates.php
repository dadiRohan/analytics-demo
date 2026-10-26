<?php

namespace App\Jobs;

use App\Models\DailyEventAggregate;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateDailyAggregates implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;

    public function handle()
    {
        // Recompute last 3 days to catch late-arriving events
        $from = now()->subDays(2)->startOfDay();
        $to   = now()->endOfDay();

        $rows = Event::whereBetween('occurred_at', [$from, $to])
            ->selectRaw(
                'DATE(occurred_at) as day, event_name,
                 MAX(event_category) as event_category,
                 COUNT(*) as total_count,
                 COUNT(DISTINCT anonymous_id) as unique_visitors'
            )
            ->groupBy('day', 'event_name')
            ->get();

        foreach ($rows as $r) {
            DailyEventAggregate::updateOrCreate(
                ['date' => $r->day, 'event_name' => $r->event_name],
                [
                    'event_category'  => $r->event_category,
                    'total_count'     => (int) $r->total_count,
                    'unique_visitors' => (int) $r->unique_visitors,
                ]
            );
        }
    }
}