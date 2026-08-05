<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * 全体管理者向けダッシュボードの横断集計を担当する。
 */
class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $totalShopsCount = Shop::query()->count();
        $activeShopsCount = Shop::query()->where('is_active', true)->count();
        $activeFacilitiesCount = Facility::query()
            ->where('is_active', true)
            ->whereHas('shop', fn ($query) => $query->where('is_active', true))
            ->count();
        $totalConfirmedReservationsCount = Reservation::query()->where('status', 'confirmed')->count();
        $totalConfirmedSales = Reservation::query()->where('status', 'confirmed')->sum('price');
        $totalRefundedAmount = DB::table('payments')->where('status', 'refunded')->sum('amount');

        // 1. 本日の予約件数（確定済み）
        $todayReservationsCount = Reservation::whereDate('start_time', $today)
            ->where('status', 'confirmed')
            ->count();

        // 2. 本日の確定済み予約データを取得（売上計算・タイムライン用）
        $todayReservations = Reservation::with(['user', 'reservable'])
            ->whereDate('start_time', $today)
            ->where('status', 'confirmed')
            ->get();

        // 3. 本日の売上合計（無料対応 'free' を除外して計算）
        $todaySales = $todayReservations->sum('price');

        // 4. 本日のキャンセル数
        $todayCancellationsCount = Reservation::whereDate('start_time', $today)
            ->whereIn('status', ['cancelled', 'canceled'])
            ->count();

        // 5. タイムライン表示用のアクティブな全施設を取得
        $facilities = Facility::with('shop:id,name,area_name')
            ->where('is_active', true)
            ->whereHas('shop', fn ($query) => $query->where('is_active', true))
            ->orderBy('name', 'asc')
            ->get();
        $todayOperatingFacilitiesCount = $todayReservations
            ->pluck('reservable_id')
            ->unique()
            ->intersect($facilities->pluck('id'))
            ->count();
        $todayFacilityUtilizationRate = $facilities->isEmpty()
            ? 0
            : round($todayOperatingFacilitiesCount / $facilities->count() * 100, 1);

        return view('admin.dashboard', compact(
            'todayReservationsCount',
            'todaySales',
            'todayCancellationsCount',
            'facilities',
            'todayReservations',
            'totalShopsCount',
            'activeShopsCount',
            'activeFacilitiesCount',
            'totalConfirmedReservationsCount',
            'totalConfirmedSales',
            'totalRefundedAmount',
            'todayOperatingFacilitiesCount',
            'todayFacilityUtilizationRate',
        ));
    }
}
