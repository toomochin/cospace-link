<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Registered; // 👈 追記
use Illuminate\Auth\Listeners\SendEmailVerificationNotification; // 👈 追記
use Illuminate\Support\Facades\Event; // 👈 追記

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 🔐 会員登録イベントが発生したら、自動的に認証メールを送信するリスナーを登録
        Event::listen(
            Registered::class,
            SendEmailVerificationNotification::class
        );
    }
}