<?php

namespace Database\Factories;

use App\Models\SearchQuery;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<SearchQuery>
 */
class SearchQueryFactory extends Factory
{
    protected $model = SearchQuery::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'query' => $this->faker->randomElement([
                'Premium polijsten',
                'Mobiele uitdeukservice',
                'Stoomreiniging',
                'Tesla detailing',
                'Interieur dieptereiniging',
            ]),
            'result_count' => $this->faker->numberBetween(0, 30),
            'timestamp' => Carbon::now()->subDays(rand(0, 29))->setTime(rand(7, 22), rand(0, 59)),
        ];
    }
}
