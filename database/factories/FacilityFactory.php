<?php

namespace Database\Factories;

use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacilityFactory extends Factory
{
    protected $model = Facility::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word() . 'スペース',
            'type' => $this->faker->randomElement(['meeting_room', 'area']),
            'capacity' => $this->faker->numberBetween(4, 30),
            'price_per_hour' => $this->faker->randomElement([500, 1000, 1500, 2000]),
            'description' => $this->faker->realText(100),
            'image_path' => null,
            'is_active' => true,
        ];
    }
}