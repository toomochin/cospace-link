<?php

namespace Tests\Unit;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_helpers_return_the_expected_values(): void
    {
        $user = User::factory()->make(['role' => 'user']);
        $owner = User::factory()->make(['role' => 'shop_owner']);
        $admin = User::factory()->make(['role' => 'system_admin']);

        $this->assertTrue($user->isUser());
        $this->assertTrue($owner->isShopOwner());
        $this->assertTrue($admin->isSystemAdmin());
        $this->assertFalse($user->isSystemAdmin());
    }

    public function test_shop_owner_belongs_to_a_shop(): void
    {
        $shop = Shop::factory()->create();
        $owner = User::factory()->create([
            'role' => 'shop_owner',
            'shop_id' => $shop->id,
        ]);

        $this->assertTrue($owner->shop->is($shop));
    }
}
