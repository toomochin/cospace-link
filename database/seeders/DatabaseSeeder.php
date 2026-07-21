<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // テスト用の一般会員を作成（ログイン確認用）
        User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'is_active' => true,
            'email_verified_at' => now(), // 最初から認証済み状態にする
        ]);

        // テスト用の管理者を作成（管理者ダッシュボード確認用）
        User::factory()->create([
            'name' => '管理者二郎',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // その他の一般ユーザーをランダムに5人作成
        User::factory(5)->create();

        // 施設（会議室・フリーエリア）データを投入
        $this->call([
            FacilitySeeder::class,
        ]);
    }
}