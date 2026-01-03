<?php

namespace Database\Factories;

use App\Models\Funnel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Funnel>
 */
class FunnelFactory extends Factory
{
    protected $model = Funnel::class;

    public function definition(): array
    {
        $step = $this->faker->randomElement([
            ['step' => 'Ontdekking', 'order' => 1],
            ['step' => 'Intenties', 'order' => 2],
            ['step' => 'Offertes', 'order' => 3],
            ['step' => 'Boekingen', 'order' => 4],
        ]);

        return [
            'user_id' => User::factory(),
            'step' => $step['step'],
            'step_order' => $step['order'],
            'timestamp' => Carbon::now()->subDays(rand(0, 14)),
        ];
    }
}
