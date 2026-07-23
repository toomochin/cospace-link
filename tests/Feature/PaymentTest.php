<?php

namespace Tests\Feature;

use App\Mail\ReservationConfirmedMail;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 8: Stripe決済完了（successコールバック）で予約がconfirmedになり確認メールが送信されるか
     */
    public function test_payment_success_confirms_reservation_and_sends_email(): void
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

        $response->assertStatus(200);

        // ステータスが confirmed に変更されたか検証
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'confirmed',
        ]);

        // 予約確定メールが送信されたか検証
        Mail::assertSent(ReservationConfirmedMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }
}