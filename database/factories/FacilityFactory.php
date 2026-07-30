<?php

namespace Database\Factories;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacilityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'name' => $this->faker->word().'スペース',
            'type' => $this->faker->randomElement(['meeting_room', 'area']),
            'capacity' => $this->faker->numberBetween(1, 30),
            'price_per_30min' => $this->faker->randomElement([300, 500, 1000]),
            'description' => $this->faker->realText(100),
            'image_path' => null,
            'is_active' => true,
        ];
    }
}
