<?php

namespace Tests\Feature\Admin;

use App\Models\Facility;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFacilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者ユーザーを作成するヘルパー
     */
    private function createAdminUser(): User
    {
        return User::factory()->create([
            'is_admin' => true, // または AdminMiddleware が参照している管理者判定フラグ・権限
            'role' => 'system_admin',
            'email_verified_at' => now(),
        ]);
    }

    /**
     * ID 12: 管理者が施設一覧（ダッシュボード等）を確認できるか
     */
    public function test_admin_can_view_facility_index(): void
    {
        $admin = $this->createAdminUser();
        $shop = Shop::factory()->create([
            'name' => '施設一覧店舗',
            'area_name' => '梅田',
        ]);
        Facility::factory()->for($shop)->create(['name' => '施設一覧会議室']);

        $response = $this->actingAs($admin)->get(route('admin.facilities.index'));

        $response->assertStatus(200)
            ->assertSee('施設一覧店舗')
            ->assertSee('梅田')
            ->assertSee('施設一覧会議室');
    }

    /**
     * ID 12: 管理者が新しい施設を追加できるか
     */
    public function test_admin_can_create_facility(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.facilities.store'), [
            'name' => '新規管理者作成会議室',
            'type' => 'meeting_room',
            'capacity' => 8,
            'price_per_30min' => 600,
            'description' => '管理者によって追加された施設です。',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('facilities', [
            'name' => '新規管理者作成会議室',
            'capacity' => 8,
        ]);
    }

    /**
     * ID 12: 管理者が施設情報を編集・利用停止（is_active=false）に切り替えられるか
     */
    public function test_admin_can_update_facility_status(): void
    {
        $admin = $this->createAdminUser();
        $facility = Facility::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->put(route('admin.facilities.update', $facility->id), [
            'name' => $facility->name,
            'type' => $facility->type,
            'capacity' => $facility->capacity,
            'price_per_30min' => $facility->price_per_30min,
            'description' => $facility->description,
            'is_active' => false, // 利用停止へ変更
        ]);

        $this->assertDatabaseHas('facilities', [
            'id' => $facility->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_filter_and_sort_facilities(): void
    {
        $admin = $this->createAdminUser();
        $shop = Shop::factory()->create(['area_name' => '渋谷']);
        $otherShop = Shop::factory()->create(['area_name' => '梅田']);
        $facility = Facility::factory()->for($shop)->create([
            'type' => 'meeting_room',
            'is_active' => true,
        ]);
        Facility::factory()->for($otherShop)->create([
            'type' => 'area',
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.facilities.index', [
            'shop_id' => $shop->id,
            'area_name' => '渋谷',
            'type' => 'meeting_room',
            'status' => 'active',
            'order' => 'name_asc',
        ]));

        $response->assertOk();
        $this->assertSame([$facility->id], $response->viewData('facilities')->pluck('id')->all());

        $orders = [
            'id_asc', 'id_desc', 'shop_asc', 'area_asc', 'name_asc', 'name_desc',
            'price_asc', 'price_desc', 'capacity_asc', 'capacity_desc', 'status_desc',
        ];

        foreach ($orders as $order) {
            $this->actingAs($admin)
                ->get(route('admin.facilities.index', ['order' => $order]))
                ->assertOk();
        }
    }

}
