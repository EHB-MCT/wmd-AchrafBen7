<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class FrontendMetricsController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'session_id' => 'nullable|string|max:64',
            'uid' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'payload' => 'nullable|array',
            'timestamp' => 'required|date',
        ]);

        $cache = Cache::store('file');

        $events = $cache->get('frontend_events', []);
        array_unshift($events, [
            'name' => $data['name'],
            'type' => $data['type'],
            'timestamp' => $data['timestamp'],
            'time_ago' => Carbon::parse($data['timestamp'])->diffForHumans(),
        ]);
        $events = array_slice($events, 0, 50);
        $cache->forever('frontend_events', $events);

        $sessions = $cache->get('frontend_sessions', []);
        $sessionKey = $data['session_id'] ?: $data['uid'];
        $now = Carbon::parse($data['timestamp']);
        $existing = $sessions[$sessionKey] ?? null;
        $firstSeen = $existing['first_seen'] ?? $data['timestamp'];
        $durationSeconds = max(
            0,
            Carbon::parse($firstSeen)->diffInSeconds($now)
        );
        $sessions[$sessionKey] = [
            'uid' => $data['uid'],
            'first_seen' => $firstSeen,
            'last_seen' => $data['timestamp'],
            'duration' => $durationSeconds,
        ];

        $sessions = array_filter($sessions, function ($session) {
            $lastSeen = Carbon::parse($session['last_seen']);
            return $lastSeen->greaterThanOrEqualTo(now()->subMinutes(10));
        });
        $cache->forever('frontend_sessions', $sessions);

        $count = $cache->increment('frontend_event_count');

        return response()->json([
            'count' => $count,
        ]);
    }

    public function show()
    {
        $cache = Cache::store('file');
        $events = $cache->get('frontend_events', []);
        $sessions = $cache->get('frontend_sessions', []);
        $count = (int) $cache->get('frontend_event_count', 0);

        $durationTotal = 0;
        $durationCount = 0;
        foreach ($sessions as $session) {
            if (! empty($session['duration'])) {
                $durationTotal += (int) $session['duration'];
                $durationCount++;
            }
        }
        $averageDuration = $durationCount > 0
            ? $this->formatDuration((int) round($durationTotal / $durationCount))
            : '0m 00s';

        $topEvent = $this->topEventName($events);

        $activity = $this->activitySeries($events);

        return response()->json([
            'total_events' => $count,
            'active_sessions' => count($sessions),
            'average_duration' => $averageDuration,
            'top_event' => $topEvent,
            'last_events' => $events,
            'activity' => $activity,
            'updated_at' => now()->toAtomString(),
        ]);
    }

    protected function activitySeries(array $events): array
    {
        $buckets = [];
        foreach ($events as $event) {
            $day = Carbon::parse($event['timestamp'])->toDateString();
            $buckets[$day] = ($buckets[$day] ?? 0) + 1;
        }

        if (empty($buckets)) {
            return [];
        }

        $dates = array_keys($buckets);
        sort($dates);

        return array_map(
            fn ($date) => [
                'date' => $date,
                'count' => (int) ($buckets[$date] ?? 0),
            ],
            $dates
        );
    }

    protected function topEventName(array $events): string
    {
        if (empty($events)) {
            return '-';
        }

        $counts = [];
        foreach ($events as $event) {
            $name = $event['name'] ?? 'event';
            $counts[$name] = ($counts[$name] ?? 0) + 1;
        }

        arsort($counts);

        return (string) array_key_first($counts);
    }

    protected function formatDuration(int $seconds): string
    {
        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes >= 60) {
            $hours = intdiv($minutes, 60);
            $minutes = $minutes % 60;
            return sprintf('%dh %02dm', $hours, $minutes);
        }

        return sprintf('%dm %02ds', $minutes, $remainingSeconds);
    }
}
