<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReservationStoreRequest;
use App\Mail\ReservationConfirmedMail;
use App\Models\Facility;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class PortalReservationController extends ReservationController
{
    public function store(
        ReservationStoreRequest $request,
        $facility_id,
    ) {
        $facility = Facility::query()
            ->where('is_active', true)
            ->whereHas('shop', fn ($query) => $query->where('is_active', true))
            ->findOrFail($facility_id);

        $validated = $request->validated();
        $startAt = Carbon::parse($validated['start_time']);
        $endAt = Carbon::parse($validated['end_time']);
        $reservedSeats = (int) $request->input('reserved_seats', $request->input('number_of_people', 1));

        if ($startAt->minute % 30 !== 0
            || $endAt->minute % 30 !== 0
            || $startAt->diffInMinutes($endAt) % 30 !== 0) {
            throw ValidationException::withMessages([
                'start_time' => '予約時間は30分単位で指定してください。',
            ]);
        }

        if ($reservedSeats < 1 || $reservedSeats > $facility->capacity) {
            throw ValidationException::withMessages([
                'reserved_seats' => "利用人数は1名以上、{$facility->capacity}名以下で指定してください。",
            ]);
        }

        $slots = (int) ($startAt->diffInMinutes($endAt) / 30);
        $totalPrice = $facility->price_per_30min * $slots;
        $paymentType = $request->input('payment_type', 'credit_card');
        $status = $paymentType === 'onsite' ? 'confirmed' : 'pending_payment';

        $reservation = app(ReservationService::class)->create(
            $facility,
            $request->user()->id,
            $startAt,
            $endAt,
            $reservedSeats,
            $totalPrice,
            $paymentType,
            $status,
        );

        if ($paymentType === 'onsite') {
            Mail::to($reservation->user)->send(new ReservationConfirmedMail($reservation));

            return view('reservations.success', compact('reservation'));
        }

        Stripe::setApiKey(config('services.stripe.secret') ?? env('STRIPE_SECRET'));

        $checkoutSession = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => "施設予約: {$facility->name}",
                        'description' => "{$startAt->format('Y/m/d H:i')} 〜 {$endAt->format('H:i')}",
                    ],
                    'unit_amount' => $totalPrice,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'client_reference_id' => (string) $reservation->id,
            'metadata' => [
                'reservation_id' => (string) $reservation->id,
            ],
            'expires_at' => now()->addMinutes(30)->timestamp,
            'success_url' => route('reservations.success', ['id' => $reservation->id]),
            'cancel_url' => route('reservations.cancel', ['id' => $reservation->id]),
        ]);

        return redirect()->away($checkoutSession->url);
    }
}
