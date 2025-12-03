<?php

namespace Database\Factories;

use App\Models\ProviderView;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<ProviderView>
 */
class ProviderViewFactory extends Factory
{
    protected $model = ProviderView::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider_id' => $this->faker->uuid(),
            'view_count' => $this->faker->numberBetween(10, 120),
            'avg_view_duration' => $this->faker->numberBetween(20, 120),
            'last_viewed_at' => Carbon::now()->subDays(rand(0, 14)),
        ];
    }
}
