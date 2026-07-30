<?php

namespace App\Services;

use Stripe\StripeClient;

class StripeRefundService
{
    public function create(string $paymentIntentId, int $amount, int $reservationId): object
    {
        $stripe = new StripeClient((string) config('services.stripe.secret'));

        return $stripe->refunds->create(
            [
                'payment_intent' => $paymentIntentId,
                'amount' => $amount,
                'metadata' => ['reservation_id' => (string) $reservationId],
            ],
            ['idempotency_key' => 'reservation-refund-'.$reservationId],
        );
    }
}
