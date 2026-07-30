<?php

namespace Tests\Feature;

use App\Mail\ReservationConfirmedMail;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 8: Stripe決済完了（successコールバック）で予約がconfirmedになり確認メールが送信されるか
     */
    public function test_success_url_does_not_confirm_a_pending_reservation(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $facility = Facility::factory()->create(['is_active' => true]);

        // 仮予約を作成 (pending_payment)
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'reservable_type' => Facility::class,
            'reservable_id' => $facility->id,
            'status' => 'pending_payment',
        ]);

        // Stripe決済成功コールバックを実行
        $response = $this->actingAs($user)->get(route('reservations.success', $reservation->id));

        $response->assertRedirect(route('reservations.index'));

        // ステータスが confirmed に変更されたか検証
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'pending_payment',
        ]);

        // 予約確定メールが送信されたか検証
        Mail::assertNothingSent();
    }

    public function test_signed_webhook_confirms_reservation_once_and_records_payment(): void
    {
        Mail::fake();
        config(['services.stripe.webhook_secret' => 'whsec_test']);
        $reservation = Reservation::factory()->create([
            'status' => 'pending_payment',
            'payment_type' => 'credit_card',
            'price' => 2400,
        ]);
        $payload = json_encode([
            'id' => 'evt_checkout_completed',
            'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'id' => 'cs_test_123',
                'payment_status' => 'paid',
                'payment_intent' => 'pi_test_123',
                'amount_total' => 2400,
                'client_reference_id' => (string) $reservation->id,
                'metadata' => ['reservation_id' => (string) $reservation->id],
            ]],
        ], JSON_THROW_ON_ERROR);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, 'whsec_test');
        $headers = ['HTTP_STRIPE_SIGNATURE' => 't='.$timestamp.',v1='.$signature];

        $this->call('POST', route('stripe.webhook'), [], [], [], $headers, $payload)->assertOk();
        $this->call('POST', route('stripe.webhook'), [], [], [], $headers, $payload)->assertOk();

        $this->assertDatabaseHas('reservations', ['id' => $reservation->id, 'status' => 'confirmed']);
        $this->assertDatabaseHas('payments', [
            'reservation_id' => $reservation->id,
            'stripe_payment_intent_id' => 'pi_test_123',
            'amount' => 2400,
            'status' => 'succeeded',
        ]);
        $this->assertDatabaseCount('processed_webhooks', 1);
        Mail::assertSent(ReservationConfirmedMail::class, 1);
    }
}
