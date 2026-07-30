<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $shop = $request->user()->shop()->withCount('facilities')->firstOrFail();
        $shopReservations = Reservation::query()
            ->whereHasMorph('reservable', [Facility::class], fn ($query) => $query->where('shop_id', $shop->id));
        $reservationCount = (clone $shopReservations)
            ->whereNotIn('status', ['cancelled', 'canceled'])
            ->count();
        $activeFacilitiesCount = Facility::query()
            ->where('shop_id', $shop->id)
            ->where('is_active', true)
            ->count();
        $todayReservations = (clone $shopReservations)
            ->with(['user', 'reservable'])
            ->whereDate('start_time', today())
            ->where('status', 'confirmed')
            ->orderBy('start_time')
            ->get();
        $todayReservationsCount = $todayReservations->count();
        $todaySales = $todayReservations->sum('price');
        $todayOperatingFacilitiesCount = $todayReservations
            ->pluck('reservable_id')
            ->unique()
            ->intersect(Facility::query()
                ->where('shop_id', $shop->id)
                ->where('is_active', true)
                ->pluck('id'))
            ->count();
        $todayFacilityUtilizationRate = $activeFacilitiesCount === 0
            ? 0
            : round($todayOperatingFacilitiesCount / $activeFacilitiesCount * 100, 1);
        $todayRefundedAmount = DB::table('payments')
            ->join('reservations', 'payments.reservation_id', '=', 'reservations.id')
            ->join('facilities', function ($join): void {
                $join->on('reservations.reservable_id', '=', 'facilities.id')
                    ->where('reservations.reservable_type', Facility::class);
            })
            ->where('facilities.shop_id', $shop->id)
            ->where('payments.status', 'refunded')
            ->whereDate('payments.updated_at', today())
            ->sum('payments.amount');
        $upcomingReservations = (clone $shopReservations)
            ->with(['user', 'reservable'])
            ->where('status', 'confirmed')
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        return view('owner.dashboard', compact(
            'shop',
            'reservationCount',
            'activeFacilitiesCount',
            'todayReservations',
            'todayReservationsCount',
            'todaySales',
            'todayRefundedAmount',
            'todayOperatingFacilitiesCount',
            'todayFacilityUtilizationRate',
            'upcomingReservations',
        ));
    }
}
