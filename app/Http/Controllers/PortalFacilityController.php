<?php

namespace App\Http\Controllers;

use App\Http\Requests\FacilitySearchRequest;
use App\Models\Facility;
use App\Support\OpeningHours;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class PortalFacilityController extends FacilityController
{
    public function index(FacilitySearchRequest $request)
    {
        $filters = $request->validated();
        $query = Facility::query()
            ->with('shop')
            ->where('facilities.is_active', true)
            ->whereHas('shop', fn (Builder $query) => $query->where('is_active', true));

        $query->when($filters['area'] ?? null, function ($query, string $area): void {
            $query->whereHas('shop', fn (Builder $shop) => $shop->where('area_name', $area));
        });

        $query->when($filters['type'] ?? null, fn (Builder $query, string $type) => $query->where('type', $type));

        foreach ($filters['amenities'] ?? [] as $amenity) {
            $query->where(function (Builder $query) use ($amenity): void {
                $query->whereJsonContains('facilities.amenities', $amenity)
                    ->orWhereHas('shop', fn (Builder $shop) => $shop->whereJsonContains('amenities', $amenity));
            });
        }

        $query->when($filters['keyword'] ?? null, function ($query, string $keyword): void {
            $escaped = addcslashes($keyword, '\\%_');
            $like = "%{$escaped}%";

            $query->where(function ($query) use ($like): void {
                $query->where('facilities.name', 'like', $like)
                    ->orWhere('facilities.description', 'like', $like)
                    ->orWhere('facilities.equipment', 'like', $like)
                    ->orWhereHas('shop', function ($shop) use ($like): void {
                        $shop->where('name', 'like', $like)
                            ->orWhere('description', 'like', $like)
                            ->orWhere('address', 'like', $like);
                    });
            });
        });

        $facilities = $query->orderBy('facilities.id')->get();

        if (isset($filters['date'], $filters['start_time'], $filters['end_time'])) {
            $startAt = Carbon::parse("{$filters['date']} {$filters['start_time']}");
            $endAt = Carbon::parse("{$filters['date']} {$filters['end_time']}");

            $facilities->load(['reservations' => function ($query) use ($startAt, $endAt): void {
                $query->whereNotIn('status', ['cancelled', 'canceled'])
                    ->where('start_time', '<', $endAt)
                    ->where('end_time', '>', $startAt);
            }]);

            $facilities = $facilities->filter(function (Facility $facility) use ($startAt, $endAt): bool {
                if (! OpeningHours::contains($facility->shop->opening_hours, $startAt, $endAt)) {
                    return false;
                }

                if (in_array($facility->type, ['meeting_room', 'room'], true)) {
                    return $facility->reservations->isEmpty();
                }

                return $facility->reservations->sum('reserved_seats') < $facility->capacity;
            })->values();
        }

        return view('welcome', compact('facilities'));
    }
}
