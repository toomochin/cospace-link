<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 4: 全スペース（会議室・エリア）を取得できるか
     * ID 4: 未ログインユーザーでもスペース一覧が確認できるか
     */
    public function test_guest_can_view_facility_list(): void
    {
        $activeFacility = Facility::factory()->create([
            'name' => 'テスト会議室A',
            'is_active' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('テスト会議室A');
    }

    /**
     * ID 4: 利用停止中のスペースはトップページの一覧に表示されない（または受付停止中と表示される）か
     */
    public function test_inactive_facility_is_not_displayed_or_shows_stopped_status(): void
    {
        $inactiveFacility = Facility::factory()->create([
            'name' => '停止中会議室B',
            'is_active' => false,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        // 一覧から除外されていることを検証する場合
        $response->assertDontSee('停止中会議室B');
    }

    /**
     * ID 6: スペース詳細情報に必要な情報が表示されるか
     */
    public function test_user_can_view_facility_detail(): void
    {
        $facility = Facility::factory()->create([
            'name' => '詳細テスト会議室',
            'description' => '快適なミーティングスペースです。',
            'capacity' => 10,
            'price_per_30min' => 500,
            'equipment' => 'Wi-Fi, プロジェクター',
            'is_active' => true,
        ]);

        $response = $this->get(route('facilities.show', $facility->id));

        $response->assertStatus(200);
        $response->assertSee('詳細テスト会議室');
        $response->assertSee('快適なミーティングスペースです。');
        $response->assertSee('10 名');
        $response->assertSee('¥500');
        $response->assertSee('Wi-Fi, プロジェクター');
    }

    /**
     * ID 5: 選択したスペースの30日先までの空き状況カレンダーが表示されるか
     */
    public function test_facility_detail_displays_availability_calendar(): void
    {
        $facility = Facility::factory()->create(['is_active' => true]);

        $response = $this->get(route('facilities.show', $facility->id));

        $response->assertStatus(200);
        $response->assertSee('空き状況カレンダー');
    }

    /**
     * ID 5: 未ログインユーザーが予約確定に進もうとした場合はログイン画面へ遷移するか
     */
    public function test_guest_is_redirected_to_login_when_submitting_reservation(): void
    {
        $facility = Facility::factory()->create(['is_active' => true]);

        $response = $this->post(route('reservations.confirm', $facility->id), [
            'date' => date('Y-m-d'),
            'start_time' => '10:00',
            'duration' => 2,
        ]);

        // 未ログイン時のリダイレクト確認
        $response->assertRedirect(route('login'));
    }
}