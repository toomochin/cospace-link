<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\VerifyEmailResponse;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // メール認証完了レスポンスを自作クラスに差し替え
        $this->app->singleton(
            VerifyEmailResponseContract::class,
            VerifyEmailResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('passkeys', function (Request $request) {
            $credentialId = $request->input('credential.id');

            return Limit::perMinute(10)->by(
                ($credentialId ?: $request->session()->getId()) . '|' . $request->ip()
            );
        });

        // ----------------------------------------------------
        // ログイン処理＆日本語バリデーションのカスタマイズ
        // ----------------------------------------------------
        Fortify::authenticateUsing(function (Request $request) {
            // 1. 未入力等の事前チェック（日本語メッセージ指定）
            Validator::make($request->all(), [
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ], [
                'email.required' => 'メールアドレスを入力してください。',
                'email.email' => '有効なメールアドレス形式で入力してください。',
                'password.required' => 'パスワードを入力してください。',
            ])->validate();

            // 2. ユーザー取得＆パスワード照合
            $user = User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            }

            // 3. 認証失敗時の日本語エラーメッセージ
            throw ValidationException::withMessages([
                Fortify::username() => ['メールアドレスまたはパスワードが正しくありません。'],
            ]);
        });

        // ログイン画面の指定
        Fortify::loginView(function () {
            return view('auth.login');
        });

        // 会員登録画面の指定
        Fortify::registerView(function () {
            return view('auth.register');
        });

        // メール認証通知画面の指定
        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });
    }
}