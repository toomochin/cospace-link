<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    /**
     * 施設一覧（トップ画面）表示
     */
    public function index(Request $request)
    {
        $query = Facility::where('is_active', true);

        // 種別絞り込み（meeting_room / area）
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // 施設名・説明文・設備備品のあいまい検索
        if ($request->filled('keyword')) {
            $keyword = '%' . $request->keyword . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', $keyword)
                    ->orWhere('description', 'like', $keyword)
                    ->orWhere('equipment', 'like', $keyword);
            });
        }

        $facilities = $query->get();

        return view('welcome', compact('facilities'));
    }

    /**
     * 施設詳細 ＆ 空き状況カレンダー表示（FN016 / FN018）
     */
    public function show($id)
    {
        $facility = Facility::where('is_active', true)->findOrFail($id);

        $now = Carbon::now();
        $startDate = Carbon::today(); // 本日 (2026-07-22 00:00:00)
        $endDate = $startDate->copy()->addDays(29)->endOfDay(); // 今日からぴったり30日目の23:59:59

        // 対象期間内の有効な予約（確定・仮予約）を取得
        $existingReservations = Reservation::where('reservable_id', $facility->id)
            ->where('reservable_type', Facility::class)
            ->whereIn('status', ['confirmed', 'pending_payment'])
            ->where('start_time', '>=', $startDate)
            ->where('end_time', '<=', $endDate)
            ->get();

        // ぴったり30日分（0日後〜29日後）のカレンダーデータ生成
        $calendarDays = [];
        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i);
            $timeSlots = [];

            // 営業時間: 9:00 〜 21:00 (30分刻み)
            $startHour = 9;
            $endHour = 21;

            $slotTime = $date->copy()->setTime($startHour, 0);
            $closingTime = $date->copy()->setTime($endHour, 0);

            while ($slotTime < $closingTime) {
                $slotEnd = $slotTime->copy()->addMinutes(30);

                // 1. 過去日時判定
                $isPast = $slotTime->isBefore($now);

                // 2. 予約重複・定員判定
                $isReserved = false;
                if ($facility->type === 'meeting_room' || $facility->type === 'room') {
                    // 会議室（個室）：同時間に1件でも予約があればNG
                    $isReserved = $existingReservations->contains(function ($res) use ($slotTime, $slotEnd) {
                        return $slotTime < Carbon::parse($res->end_time) && $slotEnd > Carbon::parse($res->start_time);
                    });
                } else {
                    // フリーアドレス（エリア）：既存予約人数の合計が定員以上ならNG
                    $currentCapacity = $existingReservations->filter(function ($res) use ($slotTime, $slotEnd) {
                        return $slotTime < Carbon::parse($res->end_time) && $slotEnd > Carbon::parse($res->start_time);
                    })->sum('reserved_seats');

                    $isReserved = $currentCapacity >= $facility->capacity;
                }

                // 予約可能フラグ
                $isAvailable = !$isPast && !$isReserved;

                $timeSlots[] = [
                    'start' => $slotTime->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                    'datetime_str' => $slotTime->format('Y-m-d H:i:s'),
                    'is_available' => $isAvailable,
                    'reason' => $isPast ? '過去' : ($isReserved ? '満杯' : '予約可'),
                ];

                $slotTime->addMinutes(30);
            }

            $calendarDays[] = [
                'date' => $date->format('Y-m-d'),
                'display' => $date->format('m/d') . '(' . $this->getJapaneseDayOfWeek($date->dayOfWeek) . ')',
                'time_slots' => $timeSlots,
            ];
        }

        return view('facilities.show', compact('facility', 'calendarDays'));
    }

    /**
     * 曜日の日本語化ヘルパー
     */
    private function getJapaneseDayOfWeek($dayOfWeek)
    {
        $days = ['日', '月', '火', '水', '木', '金', '土'];
        return $days[$dayOfWeek] ?? '';
    }
}