<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'テスト太郎',
                'password' => Hash::make('password'),
                'role' => 'user',
                'shop_id' => null,
                'is_admin' => false,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => '管理者二郎',
                'password' => Hash::make('password'),
                'role' => 'system_admin',
                'shop_id' => null,
                'is_admin' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $shibuyaShop = Shop::query()->where('name', 'CoSpace 渋谷')->firstOrFail();
        $umedaShop = Shop::query()->where('name', 'CoSpace 梅田')->firstOrFail();

        User::query()->updateOrCreate(
            ['email' => 'owner@example.com'],
            [
                'name' => '渋谷店舗管理者',
                'password' => Hash::make('password'),
                'role' => 'shop_owner',
                'shop_id' => $shibuyaShop->id,
                'is_admin' => false,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'umeda-owner@example.com'],
            [
                'name' => '梅田店舗管理者',
                'password' => Hash::make('password'),
                'role' => 'shop_owner',
                'shop_id' => $umedaShop->id,
                'is_admin' => false,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        User::factory(5)->create([
            'role' => 'user',
            'shop_id' => null,
            'is_admin' => false,
            'is_active' => true,
        ]);
    }
}
