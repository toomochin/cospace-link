<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminShopManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_admin_can_register_shop_and_invite_owner(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'system_admin']);

        $this->actingAs($admin)->post(route('admin.shops.store'), [
            'name' => 'CoSpace 新宿',
            'area_name' => '新宿',
            'owner_email' => 'shinjuku-owner@example.com',
        ])->assertRedirect(route('admin.shops.index'));

        $this->assertDatabaseHas('shops', [
            'name' => 'CoSpace 新宿',
            'area_name' => '新宿',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'shinjuku-owner@example.com',
            'role' => 'shop_owner',
        ]);

        $owner = User::query()->where('email', 'shinjuku-owner@example.com')->firstOrFail();
        Notification::assertSentTo($owner, ResetPassword::class);
    }
}
