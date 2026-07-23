<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 11: プロフィール編集画面に初期値（ユーザー名・メールアドレス）が設定されているか
     */
    public function test_profile_page_displays_current_user_information(): void
    {
        $user = User::factory()->create([
            'name' => '初期 太郎',
            'email' => 'shoki@example.com',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertSee('初期 太郎');
        $response->assertSee('shoki@example.com');
    }

    /**
     * ID 11: プロフィール情報を更新できるか
     */
    public function test_user_can_update_profile_information(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => '更新 太郎',
            'email' => 'updated@example.com',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => '更新 太郎',
            'email' => 'updated@example.com',
        ]);
    }
}