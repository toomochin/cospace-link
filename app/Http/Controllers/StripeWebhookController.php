<?php

namespace App\Http\Controllers;

use App\Mail\ReservationConfirmedMail;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use JsonException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\WebhookSignature;
use UnexpectedValueException;

/**
 * Stripe CheckoutのWebhookを受信し、予約状態と決済記録を確定する。
 *
 * 署名検証とイベントIDの記録により、改ざんと同一イベントの二重処理を防ぐ。
 */
class StripeWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $secret = config('services.stripe.webhook_secret');

        if (! is_string($secret) || $secret === '') {
            abort(500, 'Stripe webhook secret is not configured.');
        }

        try {
            $payload = $request->getContent();
            WebhookSignature::verifyHeader(
                $payload,
                (string) $request->header('Stripe-Signature'),
                $secret,
            );
            $event = json_decode($payload, false, 512, JSON_THROW_ON_ERROR);
        } catch (UnexpectedValueException|SignatureVerificationException|JsonException) {
            return response()->json(['message' => 'Invalid webhook signature.'], 400);
        }

        if ($event->type === 'checkout.session.expired') {
            $session = $event->data->object;
            $reservationId = $session->metadata->reservation_id ?? $session->client_reference_id ?? null;

            if ($reservationId) {
                DB::transaction(function () use ($event, $reservationId): void {
                    $inserted = DB::table('processed_webhooks')->insertOrIgnore([
                        'stripe_event_id' => $event->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if ($inserted === 0) {
                        return;
                    }

                    Reservation::query()
                        ->whereKey($reservationId)
                        ->where('status', 'pending_payment')
                        ->lockForUpdate()
                        ->update(['status' => 'cancelled']);
                }, 3);
            }

            return response()->json(['received' => true]);
        }

        if ($event->type !== 'checkout.session.completed') {
            return response()->json(['received' => true]);
        }

        $session = $event->data->object;
        $reservationId = $session->metadata->reservation_id ?? $session->client_reference_id ?? null;

        if (! $reservationId || ! in_array($session->payment_status, ['paid', 'no_payment_required'], true)) {
            return response()->json(['message' => 'Incomplete checkout session.'], 422);
        }

        $reservation = DB::transaction(function () use ($event, $session, $reservationId): ?Reservation {
            $inserted = DB::table('processed_webhooks')->insertOrIgnore([
                'stripe_event_id' => $event->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($inserted === 0) {
                return null;
            }

            $reservation = Reservation::query()->lockForUpdate()->findOrFail($reservationId);

            if ($reservation->status !== 'pending_payment') {
                return null;
            }

            $reservation->update(['status' => 'confirmed']);

            DB::table('payments')->updateOrInsert(
                ['reservation_id' => $reservation->id],
                [
                    'stripe_payment_intent_id' => is_string($session->payment_intent) ? $session->payment_intent : null,
                    'amount' => (int) $session->amount_total,
                    'status' => 'succeeded',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );

            return $reservation->load('user');
        }, 3);

        if ($reservation) {
            Mail::to($reservation->user)->send(new ReservationConfirmedMail($reservation));
        }

        return response()->json(['received' => true]);
    }
}
