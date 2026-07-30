<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ShopFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company().' コワーキング',
            'area_name' => fake()->city(),
            'address' => fake()->address(),
            'access' => fake()->sentence(),
            'opening_hours' => '09:00-21:00',
            'description' => fake()->realText(100),
            'image_path' => null,
            'amenities' => ['Wi-Fi', '電源'],
            'is_active' => true,
        ];
    }
}
