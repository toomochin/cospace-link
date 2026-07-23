<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ログイン状態からログアウトができるか
     */
    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        // ログイン状態にしてPOSTリクエストを送る
        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}