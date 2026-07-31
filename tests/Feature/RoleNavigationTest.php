<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_admin_navigation_contains_admin_screens(): void
    {
        $admin = User::factory()->create(['role' => 'system_admin']);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('管理メニュー')
            ->assertSee('プロフィール編集')
            ->assertSee(route('admin.shops.index'), false)
            ->assertSee(route('admin.facilities.index'), false)
            ->assertSee(route('admin.reservations.index'), false)
            ->assertSee(route('admin.users.index'), false)
            ->assertDontSee(route('owner.dashboard'), false);
    }

    public function test_shop_owner_navigation_contains_only_owner_screens(): void
    {
        $shop = Shop::factory()->create();
        $owner = User::factory()->create(['role' => 'shop_owner', 'shop_id' => $shop->id]);

        $this->actingAs($owner)->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('店舗管理')
            ->assertSee('プロフィール編集')
            ->assertSee(route('owner.facilities.index'), false)
            ->assertSee(route('owner.reservations.index'), false)
            ->assertSee(route('owner.shop.edit'), false)
            ->assertDontSee(route('admin.dashboard'), false);
    }
}
