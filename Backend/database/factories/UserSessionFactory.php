<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<UserSession>
 */
class UserSessionFactory extends Factory
{
    protected $model = UserSession::class;

    public function definition(): array
    {
        $start = Carbon::now()->subDays(rand(0, 29))->setTime(rand(7, 21), rand(0, 59));
        $duration = rand(5, 45) * 60;

        return [
            'user_id' => User::factory(),
            'start_time' => $start,
            'end_time' => $start->copy()->addSeconds($duration),
            'duration_seconds' => $duration,
            'platform' => $this->faker->randomElement(['iOS', 'Android', 'Web']),
            'network_type' => $this->faker->randomElement(['wifi', '4g', '5g']),
            'battery_level' => $this->faker->numberBetween(15, 100),
        ];
    }
}
