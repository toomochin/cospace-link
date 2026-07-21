<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class ReservationController extends Controller
{
    /**
     * 予約確認画面の表示
     */
    public function confirm(Request $request, $facility_id)
    {
        // GETアクセス（リロード等）の場合は詳細画面に戻す
        if ($request->isMethod('get')) {
            return redirect()->route('facilities.show', $facility_id);
        }

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
     * 予約の確定処理（Stripe 決済画面へリダイレクト）
     */
    public function store(Request $request, $facility_id)
    {
        $facility = Facility::where('is_active', true)->findOrFail($facility_id);

        $validated = $request->validate([
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
        ]);

        // 重複チェック
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

        // 利用コマ数（30分単位）と金額計算
        $start = Carbon::parse($validated['start_time']);
        $end = Carbon::parse($validated['end_time']);
        $slots = $start->diffInMinutes($end) / 30;
        $totalPrice = $facility->price_per_30min * $slots;

        // 1. 仮予約レコードを作成（status: pending_payment）
        $reservation = Reservation::create([
            'user_id' => $request->user()->id,
            'reservable_id' => $facility->id,
            'reservable_type' => Facility::class,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'reserved_seats' => 1,
            'status' => 'pending_payment',
        ]);

        // 2. Stripe Checkout セッションを作成
        Stripe::setApiKey(config('services.stripe.secret') ?? env('STRIPE_SECRET'));

        $checkoutSession = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'jpy',
                        'product_data' => [
                            'name' => "施設予約: {$facility->name}",
                            'description' => "{$start->format('Y/m/d H:i')} 〜 {$end->format('H:i')}",
                        ],
                        'unit_amount' => $totalPrice,
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
            'success_url' => route('reservations.success', ['id' => $reservation->id]),
            'cancel_url' => route('reservations.cancel', ['id' => $reservation->id]),
        ]);

        // 3. Stripe 決済画面へリダイレクト
        return redirect()->away($checkoutSession->url);
    }

    /**
     * 決済成功時のコールバック
     */
    public function success(Request $request, $id)
    {
        $reservation = Reservation::where('user_id', $request->user()->id)->findOrFail($id);

        // ステータスを「予約確定 (confirmed)」に変更
        $reservation->status = 'confirmed';
        $reservation->save();

        return view('reservations.success', compact('reservation'));
    }

    /**
     * 決済キャンセル時のコールバック
     */
    public function cancel(Request $request, $id)
    {
        $reservation = Reservation::where('user_id', $request->user()->id)->findOrFail($id);

        // 決済未完了のため仮予約を削除
        $reservation->delete();

        return redirect()->route('home')->with('error', '決済がキャンセルされました。');
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