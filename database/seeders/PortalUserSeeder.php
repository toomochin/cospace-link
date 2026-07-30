<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;

class PortalUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()
            ->where('email', 'admin@example.com')
            ->update(['role' => 'system_admin']);

        $shop = Shop::query()->where('name', 'CoSpace 渋谷')->firstOrFail();

        User::query()->updateOrCreate(
            ['email' => 'owner@example.com'],
            [
                'name' => '渋谷店舗管理者',
                'password' => 'password',
                'role' => 'shop_owner',
                'shop_id' => $shop->id,
                'is_admin' => false,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );
    }
}
