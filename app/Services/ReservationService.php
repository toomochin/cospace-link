<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\Reservation;
use App\Support\OpeningHours;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    public function create(
        Facility $facility,
        int $userId,
        CarbonInterface $startAt,
        CarbonInterface $endAt,
        int $reservedSeats,
        int $price,
        string $paymentType,
        string $status,
    ): Reservation {
        return DB::transaction(function () use (
            $facility,
            $userId,
            $startAt,
            $endAt,
            $reservedSeats,
            $price,
            $paymentType,
            $status,
        ): Reservation {
            $lockedFacility = Facility::query()
                ->with('shop')
                ->whereKey($facility->getKey())
                ->where('is_active', true)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedFacility->shop?->is_active
                || ! OpeningHours::contains($lockedFacility->shop->opening_hours, $startAt, $endAt)) {
                throw ValidationException::withMessages([
                    'start_time' => '店舗の営業時間内で予約時間を指定してください。',
                ]);
            }

            $overlapping = Reservation::query()
                ->where('reservable_type', Facility::class)
                ->where('reservable_id', $lockedFacility->id)
                ->whereNotIn('status', ['cancelled', 'canceled'])
                ->where('start_time', '<', $endAt)
                ->where('end_time', '>', $startAt)
                ->lockForUpdate()
                ->get();

            if (in_array($lockedFacility->type, ['meeting_room', 'room'], true) && $overlapping->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'start_time' => '指定された時間帯にはすでに別の予約が入っています。',
                ]);
            }

            if ($lockedFacility->type === 'area'
                && $overlapping->sum('reserved_seats') + $reservedSeats > $lockedFacility->capacity) {
                throw ValidationException::withMessages([
                    'reserved_seats' => '指定された時間帯は必要な席数を確保できません。',
                ]);
            }

            return Reservation::query()->create([
                'user_id' => $userId,
                'reservable_id' => $lockedFacility->id,
                'reservable_type' => Facility::class,
                'start_time' => $startAt,
                'end_time' => $endAt,
                'reserved_seats' => $reservedSeats,
                'price' => $price,
                'payment_type' => $paymentType,
                'status' => $status,
            ]);
        }, 3);
    }
}
