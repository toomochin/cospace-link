<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReservationController extends Controller
{
    /**
     * 予約確認画面の表示
     */
    public function confirm(Request $request, $facility_id)
    {
        $facility = Facility::where('is_active', true)->findOrFail($facility_id);

        $validated = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'duration' => ['required', 'integer', 'min:1', 'max:16'],
        ]);

        $startAt = Carbon::parse("{$validated['date']} {$validated['start_time']}");
        $slots = (int) $validated['duration'];
        $endAt = (clone $startAt)->addMinutes(30 * $slots);

        $totalPrice = $facility->price_per_30min * $slots;

        return view('reservations.confirm', compact('facility', 'startAt', 'endAt', 'slots', 'totalPrice'));
    }

    /**
     * 予約の確定処理（DB保存）
     */
    public function store(Request $request, $facility_id)
    {
        $facility = Facility::where('is_active', true)->findOrFail($facility_id);

        $validated = $request->validate([
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
        ]);

        // 重複チェック（Facilityモデルに対するポリモーフィック検索）
        $exists = Reservation::where('reservable_id', $facility->id)
            ->where('reservable_type', Facility::class)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']]);
            })
            ->exists();

        if ($exists) {
            return back()->withErrors(['error' => '選択した時間帯は既に予約が入っています。']);
        }

        // 予約レコード作成（既存マイグレーションのカラム名に合わせる）
        Reservation::create([
            'user_id' => $request->user()->id,
            'reservable_id' => $facility->id,
            'reservable_type' => Facility::class,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'reserved_seats' => 1,
            'status' => 'confirmed', // 一旦 confirmed で登録（将来的に決済連携で pending_payment に変更可能）
        ]);

        return redirect()->route('home')->with('status', '予約が完了しました！');
    }

    /**
     * マイページ（予約一覧）
     */
    public function index(Request $request)
    {
        $reservations = $request->user()
            ->reservations()
            ->with('reservable')
            ->orderBy('start_time', 'desc')
            ->get();

        return view('reservations.index', compact('reservations'));
    }

    /**
     * キャンセル処理
     */
    public function destroy(Request $request, $id)
    {
        $reservation = Reservation::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $reservation->status = 'cancelled';
        $reservation->save();

        return redirect()->route('reservations.index')->with('status', '予約をキャンセルしました。');
    }
}