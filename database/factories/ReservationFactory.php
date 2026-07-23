<?php

namespace Database\Factories;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        $start = Carbon::now()->addDays(2)->setHour(10)->setMinute(0)->setSecond(0);
        $end = (clone $start)->addHour();

        return [
            'user_id' => User::factory(),
            'reservable_type' => Facility::class,
            'reservable_id' => Facility::factory(),
            'start_time' => $start,
            'end_time' => $end,
            'reserved_seats' => 1,
            'status' => 'confirmed',
        ];
    }
}