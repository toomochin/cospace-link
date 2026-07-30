<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminShopValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_owner_email_is_displayed_on_registration_form(): void
    {
        $admin = User::factory()->create(['role' => 'system_admin']);
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($admin)
            ->followingRedirects()
            ->from(route('admin.shops.create'))
            ->post(route('admin.shops.store'), [
                'name' => 'CoSpace 重複確認',
                'area_name' => '渋谷',
                'owner_email' => 'existing@example.com',
            ]);

        $response->assertOk()
            ->assertSee('入力内容を確認してください。')
            ->assertSee('existing@example.com');

        $this->assertDatabaseMissing('shops', ['name' => 'CoSpace 重複確認']);
    }
}
