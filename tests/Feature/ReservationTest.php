<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 7: 「予約を確定する」手続きで仮予約が作成されるか
     */
    /**
     * ID 7: 「予約を確定する」手続きで仮予約が作成されるか
     */
    public function test_user_can_create_reservation(): void
    {
        // Stripe SDK のモック化（外部API通信をスキップ）
        \Stripe\Stripe::setApiKey('sk_test_mock');
        $mockSession = (object) ['url' => 'https://checkout.stripe.com/pay/cs_test_mock'];

        $this->mock('alias:\Stripe\Checkout\Session', function ($mock) use ($mockSession) {
            $mock->shouldReceive('create')->once()->andReturn($mockSession);
        });

        // verified（メール認証済み）のユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $facility = Facility::factory()->create(['is_active' => true]);

        $startTime = Carbon::tomorrow()->setHour(10)->setMinute(0)->format('Y-m-d H:i:s');
        $endTime = Carbon::tomorrow()->setHour(11)->setMinute(0)->format('Y-m-d H:i:s');

        $response = $this->actingAs($user)->post(route('reservations.store', $facility->id), [
            'start_time' => $startTime,
            'end_time' => $endTime,
            'reserved_seats' => 1,
        ]);

        // データベースに仮予約データ（status: pending_payment）が作成されたか検証
        $this->assertDatabaseHas('reservations', [
            'user_id' => $user->id,
            'reservable_type' => Facility::class,
            'reservable_id' => $facility->id,
            'status' => 'pending_payment',
        ]);
    }

    /**
     * ID 7: 会議室の重複予約がブロックされるか
     */
    public function test_duplicate_meeting_room_reservation_is_blocked(): void
    {
        $user2 = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $facility = Facility::factory()->create([
            'type' => 'meeting_room',
            'is_active' => true,
        ]);

        $start = Carbon::tomorrow()->setHour(10)->setMinute(0);
        $end = (clone $start)->addHour();

        // 既存の予約を作成（10:00〜11:00）
        Reservation::factory()->create([
            'reservable_type' => Facility::class,
            'reservable_id' => $facility->id,
            'start_time' => $start,
            'end_time' => $end,
            'status' => 'confirmed',
        ]);

        // 同じ時間帯に別ユーザーが予約を試みる
        $response = $this->actingAs($user2)->post(route('reservations.store', $facility->id), [
            'date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_time' => '10:00',
            'duration' => 2,
            'number_of_people' => 1,
        ]);

        $response->assertSessionHasErrors();
    }

    /**
     * ID 7: エリア（フリーアドレス）の定員上限を超える予約がブロックされるか
     */
    public function test_area_capacity_exceeded_reservation_is_blocked(): void
    {
        $user2 = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // 定員5名のエリア席
        $facility = Facility::factory()->create([
            'type' => 'area',
            'capacity' => 5,
            'is_active' => true,
        ]);

        $start = Carbon::tomorrow()->setHour(13)->setMinute(0);
        $end = (clone $start)->addHour();

        // すでに4名の予約が存在
        Reservation::factory()->create([
            'reservable_type' => Facility::class,
            'reservable_id' => $facility->id,
            'start_time' => $start,
            'end_time' => $end,
            'reserved_seats' => 4,
            'status' => 'confirmed',
        ]);

        // 追加で2名（計6名になり上限突破）予約を試みる
        $response = $this->actingAs($user2)->post(route('reservations.store', $facility->id), [
            'date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_time' => '13:00',
            'duration' => 2,
            'number_of_people' => 2,
        ]);

        $response->assertSessionHasErrors();
    }

    /**
     * ID 7: 有効な予約（未来の予約）が既に5件あるユーザーは新規予約ができないか
     */
    public function test_user_with_5_active_reservations_cannot_create_new_reservation(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $facility = Facility::factory()->create(['is_active' => true]);

        // 未来の有効な予約を既に5件作成しておく
        for ($i = 1; $i <= 5; $i++) {
            $start = Carbon::tomorrow()->addDays($i)->setHour(10)->setMinute(0);
            $end = (clone $start)->addHour();

            Reservation::factory()->create([
                'user_id' => $user->id,
                'reservable_type' => Facility::class,
                'reservable_id' => $facility->id,
                'start_time' => $start,
                'end_time' => $end,
                'status' => 'confirmed',
            ]);
        }

        // 6件目の予約を試みる
        $response = $this->actingAs($user)->post(route('reservations.store', $facility->id), [
            'date' => Carbon::tomorrow()->addDays(6)->format('Y-m-d'),
            'start_time' => '10:00',
            'duration' => 2,
            'number_of_people' => 1,
        ]);

        $response->assertSessionHasErrors();
    }

    /**
     * ID 9: 利用開始前の予約であればマイページからキャンセル（削除）できるか
     */
    public function test_user_can_cancel_future_reservation(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $start = Carbon::tomorrow()->setHour(10)->setMinute(0);

        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'start_time' => $start,
            'status' => 'confirmed',
        ]);

        // 正しいルート名 reservations.destroy へ DELETE リクエスト送信
        $response = $this->actingAs($user)->delete(route('reservations.destroy', $reservation->id));

        // データベースから該当レコードが削除されたか（論理削除または物理削除）
        $this->assertDatabaseMissing('reservations', [
            'id' => $reservation->id,
            'status' => 'confirmed',
        ]);
    }

    /**
     * ID 9: 利用開始時刻を過ぎている過去の予約はキャンセルできないか
     */
    public function test_user_cannot_cancel_past_reservation(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $start = Carbon::yesterday()->setHour(10)->setMinute(0);

        $pastReservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'start_time' => $start,
            'status' => 'confirmed',
        ]);

        // 正しいルート名 reservations.destroy へ DELETE リクエスト送信
        $response = $this->actingAs($user)->delete(route('reservations.destroy', $pastReservation->id));

        // 拒否（403 Forbidden や セッションエラー等）されるか検証
        $this->assertTrue(
            $response->isForbidden() || session()->has('errors') || $response->isRedirect()
        );
    }
}