<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_are_redirected_to_their_dashboard_after_login(): void
    {
        $shop = Shop::factory()->create();
        $cases = [
            ['user', null, 'home'],
            ['shop_owner', $shop->id, 'owner.dashboard'],
            ['system_admin', null, 'admin.dashboard'],
        ];

        foreach ($cases as [$role, $shopId, $route]) {
            $user = User::factory()->create([
                'email' => "{$role}@example.com",
                'password' => 'password',
                'role' => $role,
                'shop_id' => $shopId,
            ]);

            $this->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ])->assertRedirect(route($route));

            $this->post('/logout');
        }
    }

    public function test_shop_owner_cannot_access_system_admin_routes(): void
    {
        $owner = User::factory()->create([
            'role' => 'shop_owner',
            'shop_id' => Shop::factory(),
        ]);

        $this->actingAs($owner)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_system_admin_cannot_access_owner_routes(): void
    {
        $admin = User::factory()->create(['role' => 'system_admin']);

        $this->actingAs($admin)->get(route('owner.dashboard'))->assertForbidden();
    }

    public function test_owner_cannot_update_another_shops_facility(): void
    {
        $ownedShop = Shop::factory()->create();
        $otherFacility = Facility::factory()->create();
        $owner = User::factory()->create([
            'role' => 'shop_owner',
            'shop_id' => $ownedShop->id,
        ]);

        $this->actingAs($owner)
            ->put(route('owner.facilities.update', $otherFacility), [
                'name' => '不正な更新',
                'type' => 'meeting_room',
                'price_per_30min' => 500,
                'capacity' => 4,
                'is_active' => true,
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('facilities', [
            'id' => $otherFacility->id,
            'name' => '不正な更新',
        ]);
    }
}
