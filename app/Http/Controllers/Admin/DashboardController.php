<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Reservation;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

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
        $todaySales = $todayReservations->sum(function ($reservation) {
            if ($reservation->payment_type === 'free') {
                return 0;
            }

            $start = Carbon::parse($reservation->start_time);
            $end = Carbon::parse($reservation->end_time);
            $slots = $start->diffInMinutes($end) / 30;

            return ($reservation->reservable->price_per_30min ?? 0) * $slots;
        });

        // 4. 本日のキャンセル数
        $todayCancellationsCount = Reservation::whereDate('start_time', $today)
            ->whereIn('status', ['cancelled', 'canceled'])
            ->count();

        // 5. タイムライン表示用のアクティブな全施設を取得
        $facilities = Facility::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.dashboard', compact(
            'todayReservationsCount',
            'todaySales',
            'todayCancellationsCount',
            'facilities',
            'todayReservations'
        ));
    }
}