<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Funnel;
use App\Models\Insight;
use App\Models\ProviderView;
use App\Models\SearchQuery;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class MockAnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        if (User::count() === 0) {
            User::factory()->create([
                'name' => 'Productteam',
                'email' => 'team@nios.app',
            ]);
        }

        $extraUsers = max(0, 6 - User::count());
        if ($extraUsers > 0) {
            User::factory($extraUsers)->create();
        }

        $users = User::all();

        $this->seedSessionsAndEvents($users);
        $this->seedSearchQueries($users);
        $this->seedProviderViews($users);
        $this->seedFunnels($users);
        $this->seedInsights($users);
    }

    protected function seedSessionsAndEvents($users): void
    {
        $end = Carbon::now();
        $start = $end->copy()->subDays(29);
        $screens = ['home', 'providers', 'provider.detail', 'booking', 'search'];
        $eventNames = [
            'view' => ['screen.view', 'provider.view', 'list.scrolled'],
            'click' => ['button.primary', 'cta.start-booking', 'filter.apply'],
            'search' => ['search.performed'],
            'conversion' => ['booking.completed', 'quote.accepted'],
        ];

        foreach ($users as $user) {
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                if (rand(0, 100) < 45) {
                    continue;
                }

                $sessionCount = rand(1, 2);
                for ($i = 0; $i < $sessionCount; $i++) {
                    $sessionStart = $date->copy()->setTime(rand(8, 21), rand(0, 59));
                    $duration = rand(5, 40) * 60;
                    $session = UserSession::factory()->create([
                        'user_id' => $user->id,
                        'start_time' => $sessionStart,
                        'end_time' => $sessionStart->copy()->addSeconds($duration),
                        'duration_seconds' => $duration,
                        'platform' => Arr::random(['iOS', 'Android', 'Web']),
                        'network_type' => Arr::random(['wifi', '4g', '5g']),
                        'battery_level' => rand(25, 100),
                    ]);

                    $eventsToCreate = rand(4, 10);
                    for ($j = 0; $j < $eventsToCreate; $j++) {
                        $type = Arr::random(['view', 'click', 'search', rand(0, 100) > 70 ? 'conversion' : 'click']);
                        $timestamp = $sessionStart->copy()->addSeconds(rand(60, max(120, $duration - 60)));

                        Event::factory()->create([
                            'session_id' => $session->id,
                            'user_id' => $user->id,
                            'type' => $type,
                            'name' => Arr::random($eventNames[$type] ?? $eventNames['view']),
                            'value' => [
                                'screen' => Arr::random($screens),
                                'label' => Arr::random($eventNames[$type] ?? ['interaction']),
                            ],
                            'device_x' => rand(20, 360),
                            'device_y' => rand(40, 740),
                            'timestamp' => $timestamp,
                            'created_at' => $timestamp,
                        ]);
                    }
                }
            }
        }
    }

    protected function seedSearchQueries($users): void
    {
        $phrases = [
            ['phrase' => 'Premium polijsten', 'base' => 1200],
            ['phrase' => 'Mobiele uitdeukservice', 'base' => 980],
            ['phrase' => 'Stoomreiniging', 'base' => 870],
            ['phrase' => 'Tesla detailing', 'base' => 760],
            ['phrase' => 'Ceramic coating', 'base' => 680],
        ];

        foreach ($phrases as $phrase) {
            $entries = rand(18, 28);
            for ($i = 0; $i < $entries; $i++) {
                $timestamp = Carbon::now()->subDays(rand(0, 20))->setTime(rand(7, 23), rand(0, 59));
                SearchQuery::factory()->create([
                    'user_id' => $users->random()->id,
                    'query' => $phrase['phrase'],
                    'result_count' => rand(0, 35),
                    'timestamp' => $timestamp,
                ]);
            }
        }
    }

    protected function seedProviderViews($users): void
    {
        $providers = ['LuxeWash', 'AutoSpa', 'CeramicPro', 'CleanFleet'];

        foreach ($providers as $provider) {
            ProviderView::factory()->create([
                'user_id' => $users->random()->id,
                'provider_id' => $provider,
                'view_count' => rand(120, 420),
                'avg_view_duration' => rand(25, 140),
                'last_viewed_at' => Carbon::now()->subDays(rand(0, 5)),
            ]);
        }
    }

    protected function seedFunnels($users): void
    {
        $steps = [
            ['step' => 'Ontdekking', 'order' => 1],
            ['step' => 'Intenties', 'order' => 2],
            ['step' => 'Offertes', 'order' => 3],
            ['step' => 'Boekingen', 'order' => 4],
        ];

        foreach ($users as $user) {
            $entered = Carbon::now()->subDays(rand(1, 14));
            foreach ($steps as $index => $step) {
                if ($index > 0 && rand(0, 100) < (20 + ($index * 10))) {
                    break;
                }

                Funnel::factory()->create([
                    'user_id' => $user->id,
                    'step' => $step['step'],
                    'step_order' => $step['order'],
                    'timestamp' => $entered->copy()->addMinutes($index * 10),
                ]);
            }
        }
    }

    protected function seedInsights($users): void
    {
        foreach ($users as $user) {
            Insight::updateOrCreate(
                ['user_id' => $user->id],
                Insight::factory()->make(['user_id' => $user->id])->toArray()
            );
        }
    }
}
