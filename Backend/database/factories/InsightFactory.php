<?php

namespace Database\Factories;

use App\Models\Insight;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Insight>
 */
class InsightFactory extends Factory
{
    protected $model = Insight::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'impulsivity_score' => $this->faker->numberBetween(10, 90),
            'hesitation_score' => $this->faker->numberBetween(10, 90),
            'premium_tendency' => $this->faker->numberBetween(10, 90),
            'night_user' => $this->faker->boolean(20),
            'likely_to_book' => $this->faker->numberBetween(20, 95),
            'risk_churn' => $this->faker->numberBetween(5, 70),
        ];
    }
}
