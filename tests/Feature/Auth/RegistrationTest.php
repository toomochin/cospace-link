<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 名前が未入力の場合はエラーになるか
     */
    public function test_name_is_required(): void
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /**
     * メールアドレスが未入力の場合はエラーになるか
     */
    public function test_email_is_required(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * パスワードが未入力の場合はエラーになるか
     */
    public function test_password_is_required(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /**
     * パスワードが7文字以下の場合はエラーになるか (8文字以上必要)
     */
    public function test_password_must_be_at_least_8_characters(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'pass123', // 7文字
            'password_confirmation' => 'pass123',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /**
     * パスワード確認用と一致しない場合はエラーになるか
     */
    public function test_password_must_match_confirmation(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /**
     * 正しい入力でユーザー登録され、プロフィール設定画面へ遷移するか
     * 会員登録後に認証メールが送信されるか (仕様書 ID 13)
     */
    public function test_new_users_can_register_and_receive_verification_email(): void
    {
        Event::fake();

        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();

        // データベースに登録されているか確認
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // Registeredイベント（メール認証送信のトリガー）が発生したか検証
        Event::assertDispatched(Registered::class);

        // プロフィール設定画面（または意図したリダイレクト先）に遷移するか確認
        $response->assertRedirect(route('home'));
    }
}