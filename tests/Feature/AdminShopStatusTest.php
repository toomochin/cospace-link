<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminShopStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_admin_can_stop_and_republish_a_shop(): void
    {
        $admin = User::factory()->create(['role' => 'system_admin']);
        $shop = Shop::factory()->create(['is_active' => true]);

        $this->actingAs($admin)
            ->patch(route('admin.shops.toggle-status', $shop))
            ->assertRedirect(route('admin.shops.index'))
            ->assertSessionHas('status', '加盟店舗を掲載停止にしました。');
        $this->assertFalse($shop->refresh()->is_active);

        $this->actingAs($admin)
            ->patch(route('admin.shops.toggle-status', $shop))
            ->assertRedirect(route('admin.shops.index'))
            ->assertSessionHas('status', '加盟店舗を再掲載しました。');
        $this->assertTrue($shop->refresh()->is_active);
    }
}
