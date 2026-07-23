<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 10: マイページで必要な情報が表示されるか
     */
    public function test_user_can_view_mypage_with_reservations(): void
    {
        $user = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
        ]);

        $facility = Facility::factory()->create(['name' => '渋谷オフィスA']);

        // これからの予約（未来）
        Reservation::factory()->create([
            'user_id' => $user->id,
            'reservable_type' => Facility::class,
            'reservable_id' => $facility->id,
            'start_time' => Carbon::tomorrow()->setHour(14)->setMinute(0),
            'end_time' => Carbon::tomorrow()->setHour(15)->setMinute(0),
            'status' => 'confirmed',
        ]);

        // 過去の利用履歴
        Reservation::factory()->create([
            'user_id' => $user->id,
            'reservable_type' => Facility::class,
            'reservable_id' => $facility->id,
            'start_time' => Carbon::yesterday()->setHour(10)->setMinute(0),
            'end_time' => Carbon::yesterday()->setHour(11)->setMinute(0),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($user)->get(route('reservations.index'));

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('渋谷オフィスA');
    }
}