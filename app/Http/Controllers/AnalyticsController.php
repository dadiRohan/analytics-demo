<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DailyEventAggregate;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function overview(Request $request)
    {
        [$from, $to] = $this->range($request);

        $pageViews = DailyEventAggregate::where('event_name', 'page_view')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->sum('total_count');

        $uniqueVisitors = Event::where('event_name', 'page_view')
            ->whereBetween('occurred_at', [$from, $to])
            ->distinct('anonymous_id')
            ->count('anonymous_id');

        $sessions = Event::where('event_name', 'page_view')
            ->whereBetween('occurred_at', [$from, $to])
            ->distinct('session_id')
            ->count('session_id');

        return response()->json([
            'page_views'      => (int) $pageViews,
            'unique_visitors' => (int) $uniqueVisitors,
            'sessions'        => (int) $sessions,
        ]);
    }

    public function trend(Request $request)
    {
        [$from, $to] = $this->range($request);
        $eventName = $request->query('event_name');

        $rows = DailyEventAggregate::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->when($eventName, fn ($q) => $q->where('event_name', $eventName))
            ->orderBy('date')
            ->get();

        return response()->json(
            $rows->groupBy(fn ($r) => $r->date->toDateString())
                 ->map(fn ($items, $date) => ['date' => $date, 'count' => $items->sum('total_count')])
                 ->values()
        );
    }

    public function popularPages(Request $request)
    {
        [$from, $to] = $this->range($request);
        $limit = (int) $request->query('limit', 10);

        return response()->json(
            Event::where('event_name', 'page_view')
                ->whereBetween('occurred_at', [$from, $to])
                ->select('properties', 'page_url')
                ->get()
                ->groupBy(function ($e) {
                    return $e->properties['path']
                        ?? (parse_url($e->page_url ?? '', PHP_URL_PATH) ?: '/');
                })
                ->map(fn ($items, $path) => ['page' => $path, 'views' => $items->count()])
                ->sortByDesc('views')
                ->take($limit)
                ->values()
        );
    }

    public function eventBreakdown(Request $request)
    {
        [$from, $to] = $this->range($request);

        return response()->json(
            DailyEventAggregate::whereBetween('date', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('event_name, SUM(total_count) as count')
                ->groupBy('event_name')
                ->orderByDesc('count')
                ->get()
        );
    }

    private function range(Request $request): array
    {
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : now()->endOfDay();
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : now()->subDays(6)->startOfDay();

        return [$from, $to];
    }
}