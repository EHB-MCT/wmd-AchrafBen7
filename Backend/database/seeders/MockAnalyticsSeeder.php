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
        $screens = ['home', 'providers', 'provider.detail', 'booking', 'search', 'profile', 'settings'];
        $eventDistribution = [
            ['type' => 'view', 'names' => ['screen.view', 'provider.view', 'list.scrolled', 'provider.gallery'], 'weight' => 6],
            ['type' => 'click', 'names' => ['button.primary', 'cta.start-booking', 'filter.apply', 'provider.call', 'provider.favorite'], 'weight' => 5],
            ['type' => 'search', 'names' => ['search.performed', 'search.refine', 'search.autocomplete'], 'weight' => 3],
            ['type' => 'navigation', 'names' => ['nav.drawer.open', 'nav.tab.switch'], 'weight' => 2],
            ['type' => 'scroll', 'names' => ['scroll.fast', 'scroll.section'], 'weight' => 2],
            ['type' => 'error', 'names' => ['error.validation', 'error.network'], 'weight' => 1],
            ['type' => 'conversion', 'names' => ['booking.completed', 'quote.accepted', 'appointment.confirmed'], 'weight' => 2],
        ];

        $weightedEvents = [];
        foreach ($eventDistribution as $definition) {
            for ($i = 0; $i < $definition['weight']; $i++) {
                $weightedEvents[] = $definition;
            }
        }

        $hotZones = [
            ['x' => 80, 'y' => 620, 'spread' => 40],
            ['x' => 190, 'y' => 420, 'spread' => 35],
            ['x' => 320, 'y' => 280, 'spread' => 30],
            ['x' => 150, 'y' => 180, 'spread' => 25],
        ];

        foreach ($users as $user) {
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                if (rand(0, 100) < 30) {
                    continue;
                }

                $sessionCount = rand(2, 4);
                for ($i = 0; $i < $sessionCount; $i++) {
                    $sessionStart = $date->copy()->setTime(rand(8, 21), rand(0, 59));
                    $duration = rand(5, 55) * 60;
                    $session = UserSession::factory()->create([
                        'user_id' => $user->id,
                        'start_time' => $sessionStart,
                        'end_time' => $sessionStart->copy()->addSeconds($duration),
                        'duration_seconds' => $duration,
                        'platform' => Arr::random(['iOS', 'Android', 'Web']),
                        'network_type' => Arr::random(['wifi', '4g', '5g']),
                        'battery_level' => rand(25, 100),
                    ]);

                    $eventsToCreate = rand(10, 24);
                    $currentScreen = Arr::random($screens);

                    for ($j = 0; $j < $eventsToCreate; $j++) {
                        $definition = Arr::random($weightedEvents);
                        $type = $definition['type'];
                        $timestamp = $sessionStart->copy()->addSeconds(rand(30, max(120, $duration - 60)));
                        $zone = Arr::random($hotZones);
                        $deviceX = max(10, min(375, (int) round($zone['x'] + rand(-$zone['spread'], $zone['spread']))));
                        $deviceY = max(10, min(812, (int) round($zone['y'] + rand(-$zone['spread'], $zone['spread']))));

                        if ($type === 'navigation') {
                            $currentScreen = Arr::random($screens);
                        }

                        $value = [
                            'screen' => $currentScreen,
                            'label' => Arr::random($definition['names']),
                        ];

                        if ($type === 'search') {
                            $value['query'] = Arr::random(['Tesla', 'Polijsten', 'Keramisch', 'Snelle was', 'Interieur']);
                        }

                        if ($type === 'conversion') {
                            $value['amount'] = rand(80, 320);
                            $currentScreen = 'booking';
                        }

                        Event::factory()->create([
                            'session_id' => $session->id,
                            'user_id' => $user->id,
                            'type' => $type,
                            'name' => Arr::random($definition['names']),
                            'value' => $value,
                            'device_x' => $deviceX,
                            'device_y' => $deviceY,
                            'timestamp' => $timestamp,
                            'created_at' => $timestamp,
                        ]);
                    }

                    if (rand(0, 100) > 65) {
                        $timestamp = $sessionStart->copy()->addSeconds($duration - rand(30, 120));
                        Event::factory()->create([
                            'session_id' => $session->id,
                            'user_id' => $user->id,
                            'type' => 'conversion',
                            'name' => Arr::random(['booking.completed', 'quote.accepted']),
                            'value' => [
                                'screen' => 'booking',
                                'label' => 'booking.completed',
                                'amount' => rand(120, 420),
                            ],
                            'device_x' => rand(40, 330),
                            'device_y' => rand(180, 760),
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
            'Premium polijsten',
            'Mobiele uitdeukservice',
            'Stoomreiniging',
            'Tesla detailing',
            'Ceramic coating',
            'Interieur dieptereiniging',
            'Velgen refurbish',
            'Lakcorrectie',
            'Snelle onderhoudsbeurt',
        ];

        for ($day = 0; $day < 30; $day++) {
            $dayTimestamp = Carbon::now()->subDays($day);
            $queriesToday = rand(6, 12);

            for ($i = 0; $i < $queriesToday; $i++) {
                $timestamp = $dayTimestamp->copy()->setTime(rand(7, 22), rand(0, 59));
                SearchQuery::factory()->create([
                    'user_id' => $users->random()->id,
                    'query' => Arr::random($phrases),
                    'result_count' => rand(0, 40),
                    'timestamp' => $timestamp,
                ]);
            }
        }
    }

    protected function seedProviderViews($users): void
    {
        $providers = ['LuxeWash', 'AutoSpa', 'CeramicPro', 'CleanFleet', 'DiamondDetail', 'EcoWash', 'SpeedyDetail'];

        foreach ($providers as $provider) {
            foreach (range(1, rand(2, 4)) as $iteration) {
                ProviderView::factory()->create([
                    'user_id' => $users->random()->id,
                    'provider_id' => $provider,
                    'view_count' => rand(80, 520),
                    'avg_view_duration' => rand(25, 180),
                    'last_viewed_at' => Carbon::now()->subDays(rand(0, 5)),
                ]);
            }
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
            foreach (range(1, rand(2, 4)) as $iteration) {
                foreach ($steps as $index => $step) {
                    if ($index > 0 && rand(0, 100) < (15 + ($index * 12))) {
                        break;
                    }

                    Funnel::factory()->create([
                        'user_id' => $user->id,
                        'step' => $step['step'],
                        'step_order' => $step['order'],
                        'timestamp' => $entered->copy()->addMinutes(($iteration * 45) + ($index * 10)),
                    ]);
                }
            }
        }
    }

    protected function seedInsights($users): void
    {
        foreach ($users as $user) {
            Insight::updateOrCreate(
                ['user_id' => $user->id],
                array_merge(
                    Insight::factory()->make(['user_id' => $user->id])->toArray(),
                    ['updated_at' => Carbon::now()->subDays(rand(0, 5))]
                )
            );
        }
    }
}
