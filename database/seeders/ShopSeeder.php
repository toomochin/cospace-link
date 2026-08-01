<?php

namespace Database\Seeders;

use App\Models\Shop;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        $shops = [
            [
                'name' => 'CoSpace 渋谷', 'area_name' => '渋谷',
                'address' => '東京都渋谷区渋谷1-1-1', 'access' => '渋谷駅から徒歩5分',
                'opening_hours' => '09:00-21:00',
                'description' => 'Web会議にも集中作業にも使いやすい都市型ワークスペースです。',
                'image_path' => 'images/shops/shibuya.jpg',
                'amenities' => ['Wi-Fi', '電源'], 'is_active' => true,
            ],
            [
                'name' => 'CoSpace 梅田', 'area_name' => '梅田',
                'address' => '大阪府大阪市北区梅田1-1-1', 'access' => '大阪梅田駅から徒歩4分',
                'opening_hours' => '08:00-22:00',
                'description' => '会議室とフリーアドレス席を備えた大型店舗です。',
                'image_path' => 'images/shops/umeda.jpg',
                'amenities' => ['Wi-Fi', '電源', 'フリードリンク'], 'is_active' => true,
            ],
        ];

        foreach ($shops as $shop) {
            Shop::query()->updateOrCreate(['name' => $shop['name']], $shop);
        }
    }
}
