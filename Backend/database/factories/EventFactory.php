<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $timestamp = Carbon::now()->subDays(rand(0, 29))->setTime(rand(8, 22), rand(0, 59));
        $types = ['view', 'click', 'search', 'conversion'];
        $type = Arr::random($types);

        return [
            'session_id' => UserSession::factory(),
            'user_id' => User::factory(),
            'type' => $type,
            'name' => $this->fakeEventName($type),
            'value' => [
                'screen' => $this->faker->randomElement(['home', 'providers', 'booking', 'settings']),
                'label' => $this->faker->sentence(3),
            ],
            'device_x' => $this->faker->numberBetween(0, 375),
            'device_y' => $this->faker->numberBetween(0, 812),
            'timestamp' => $timestamp,
            'created_at' => $timestamp,
        ];
    }

    protected function fakeEventName(string $type): string
    {
        return match ($type) {
            'conversion' => $this->faker->randomElement(['booking.completed', 'quote.accepted']),
            'search' => 'search.performed',
            'click' => $this->faker->randomElement(['button.primary', 'card.opened', 'cta.tapped']),
            default => $this->faker->randomElement(['screen.view', 'provider.view', 'home.load']),
        };
    }
}
