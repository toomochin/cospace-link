<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // テスト用の一般会員を作成（ログイン確認用）
        $testUser = User::factory()->create([
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
        $randomUsers = User::factory(5)->create();

        // 一般ユーザー全員をひとまとめにしておく
        $allUsers = $randomUsers->concat([$testUser]);

        // 施設（会議室・フリーエリア）データを投入
        $this->call([
            FacilitySeeder::class,
        ]);

        // 登録された施設を取得
        $facilities = Facility::all();

        // 施設データが存在する場合のみ予約ダミーデータを投入
        if ($facilities->isNotEmpty()) {
            $statuses = ['confirmed', 'confirmed', 'confirmed', 'pending_payment'];

            // 15件の予約ダミーを作成
            for ($i = 0; $i < 15; $i++) {
                $user = $allUsers->random();
                $facility = $facilities->random();

                // 過去〜未来（前後7日間）のランダムな日時を作成
                $daysOffset = rand(-7, 7);
                $startHour = rand(9, 18);
                $duration30MinChunks = rand(1, 4); // 30分〜2時間

                $startTime = Carbon::today()->addDays($daysOffset)->setHour($startHour)->setMinute(0);
                $endTime = (clone $startTime)->addMinutes($duration30MinChunks * 30);

                // エリア席の場合は1〜3名、個室/会議室は1名
                $seats = ($facility->type === 'area') ? rand(1, 3) : 1;

                Reservation::create([
                    'user_id' => $user->id,
                    'reservable_type' => Facility::class,
                    'reservable_id' => $facility->id,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'reserved_seats' => $seats,
                    'status' => $statuses[array_rand($statuses)],
                    'created_at' => $startTime->copy()->subDays(rand(1, 3)),
                ]);
            }
        }
    }
}