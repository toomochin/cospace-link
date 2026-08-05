<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReservationStoreRequest;
use App\Mail\ReservationCancelledMail;
use App\Mail\ReservationConfirmedMail;
use App\Models\Facility;
use App\Models\Reservation;
use App\Services\StripeRefundService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;
use Throwable;

/**
 * 会員向けの予約確認・作成・一覧・キャンセル処理を担当する。
 *
 * 予約競合の判定と決済処理は専用サービスへ委譲する。
 */
class ReservationController extends Controller
{
    /**
     * 予約確認画面の表示
     */
    public function confirm(Request $request, $facility_id)
    {
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

        // 重複チェック
        $exists = Reservation::where('reservable_id', $facility->id)
            ->where('reservable_type', Facility::class)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startAt, $endAt) {
                $query->where('start_time', '<', $endAt)
                    ->where('end_time', '>', $startAt);
            })
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['start_time' => '指定された時間帯にはすでに別の予約が入っています。']);
        }

        $totalPrice = $facility->price_per_30min * $slots;

        return view('reservations.confirm', compact('facility', 'startAt', 'endAt', 'slots', 'totalPrice'));
    }

    /**
     * 予約の確定処理（クレカ or 現地払いの分岐）
     */
    public function store(ReservationStoreRequest $request, $facility_id)
    {
        $facility = Facility::where('is_active', true)->findOrFail($facility_id);

        $validated = $request->validated();

        // 1. お支払い方法の取得
        $paymentType = $request->input('payment_type', 'credit_card');

        // 2. 金額の取得（確認画面から送られた total_price を最優先）
        $totalPrice = $request->input('total_price');

        // もし total_price が送られてこなかった場合のみ安全に再計算
        if (is_null($totalPrice) || $totalPrice === '') {
            $start = Carbon::parse($validated['start_time']);
            $end = Carbon::parse($validated['end_time']);
            $minutes = $start->diffInMinutes($end);
            $slots = max(1, (int) ceil($minutes / 30)); // 最低1コマを保証
            $totalPrice = $facility->price_per_30min * $slots;
        }

        // ★ 現地払いの処理
        if ($paymentType === 'onsite') {
            $reservation = Reservation::create([
                'user_id' => $request->user()->id,
                'reservable_id' => $facility->id,
                'reservable_type' => Facility::class,
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'reserved_seats' => 1,
                'price' => $totalPrice, // ★ 確実に金額が入ります
                'payment_type' => 'onsite',
                'status' => 'confirmed',
            ]);

            // 完了メールの送信
            Mail::to($reservation->user)->send(new ReservationConfirmedMail($reservation));

            return view('reservations.success', compact('reservation'));
        }

        // ★ クレジットカード決済の処理（Stripe）
        $reservation = Reservation::create([
            'user_id' => $request->user()->id,
            'reservable_id' => $facility->id,
            'reservable_type' => Facility::class,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'reserved_seats' => 1,
            'price' => $totalPrice, // ★ 確実に金額が入ります
            'payment_type' => 'credit_card',
            'status' => 'pending_payment',
        ]);

        Stripe::setApiKey(config('services.stripe.secret') ?? env('STRIPE_SECRET'));

        $start = Carbon::parse($validated['start_time']);
        $end = Carbon::parse($validated['end_time']);

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
                ],
            ],
            'mode' => 'payment',
            'success_url' => route('reservations.success', ['id' => $reservation->id]),
            'cancel_url' => route('reservations.cancel', ['id' => $reservation->id]),
        ]);

        return redirect()->away($checkoutSession->url);
    }

    /**
     * 決済成功時のコールバック（クレカ用）
     */
    public function success(Request $request, $id)
    {
        $reservation = Reservation::where('user_id', $request->user()->id)->findOrFail($id);

        if ($reservation->status === 'pending_payment') {
            return redirect()->route('reservations.index')->with(
                'status',
                '決済結果を確認しています。しばらくしてから予約状況をご確認ください。',
            );
        }

        return view('reservations.success', compact('reservation'));
    }

    /**
     * 決済キャンセル時のコールバック（クレカ用）
     */
    public function cancel(Request $request, $id)
    {
        $reservation = Reservation::where('user_id', $request->user()->id)->findOrFail($id);

        // 決済未完了の場合のみ仮予約を削除
        if ($reservation->status === 'pending_payment') {
            $reservation->delete();
        }

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
    public function destroy(Request $request, $id, StripeRefundService $refundService)
    {
        $reservation = Reservation::where('user_id', $request->user()->id)
            ->findOrFail($id);

        if ($reservation->status !== 'confirmed') {
            throw ValidationException::withMessages([
                'reservation' => '確定済みの予約のみキャンセルできます。',
            ]);
        }

        if (now()->addHours(24)->gt($reservation->start_time)) {
            throw ValidationException::withMessages([
                'reservation' => 'キャンセルは利用開始時刻の24時間前まで可能です。',
            ]);
        }

        try {
            DB::transaction(function () use ($reservation, $refundService): void {
                $lockedReservation = Reservation::query()->lockForUpdate()->findOrFail($reservation->id);

                if ($lockedReservation->status !== 'confirmed') {
                    throw ValidationException::withMessages([
                        'reservation' => 'この予約はすでにキャンセルされています。',
                    ]);
                }

                if ($lockedReservation->payment_type === 'credit_card') {
                    $payment = DB::table('payments')
                        ->where('reservation_id', $lockedReservation->id)
                        ->where('status', 'succeeded')
                        ->lockForUpdate()
                        ->first();

                    if (! $payment?->stripe_payment_intent_id) {
                        throw ValidationException::withMessages([
                            'reservation' => '決済情報が確認できないため返金できません。',
                        ]);
                    }

                    $refund = $refundService->create(
                        $payment->stripe_payment_intent_id,
                        (int) $payment->amount,
                        $lockedReservation->id,
                    );

                    DB::table('payments')->where('id', $payment->id)->update([
                        'stripe_refund_id' => $refund->id,
                        'status' => 'refunded',
                        'updated_at' => now(),
                    ]);
                }

                $lockedReservation->update(['status' => 'cancelled']);
            }, 3);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'reservation' => '返金処理に失敗しました。時間をおいて再度お試しください。',
            ]);
        }

        // キャンセル完了メールの送信
        Mail::to($request->user())->send(new ReservationCancelledMail($reservation));

        return redirect()->route('reservations.index')->with('status', '予約をキャンセルしました。');
    }
}
