<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Shop;
use Illuminate\Database\Seeder;
use RuntimeException;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $shops = Shop::query()
            ->whereIn('name', ['CoSpace 渋谷', 'CoSpace 梅田'])
            ->get()
            ->keyBy('name');

        if ($shops->count() !== 2) {
            throw new RuntimeException('ShopSeeder must run before FacilitySeeder.');
        }

        $facilities = [
            ['shop' => 'CoSpace 渋谷', 'name' => '会議室A', 'type' => 'meeting_room', 'price_per_30min' => 750, 'capacity' => 6, 'equipment' => 'ホワイトボード、大型モニター', 'amenities' => ['モニター', 'ホワイトボード', 'Web会議ブース可'], 'description' => 'Web会議にも使える個室です。', 'image_path' => 'images/facilities/shibuya-meeting-a.jpg', 'is_active' => true],
            ['shop' => 'CoSpace 渋谷', 'name' => '防音個室ブース', 'type' => 'meeting_room', 'price_per_30min' => 300, 'capacity' => 1, 'equipment' => '防音、電源、高速Wi-Fi', 'amenities' => ['防音', 'Web会議ブース可'], 'description' => 'メンテナンス中のWeb会議用ブースです。', 'image_path' => 'images/facilities/shibuya-booth.jpg', 'is_active' => false],
            ['shop' => 'CoSpace 梅田', 'name' => '大会議室', 'type' => 'meeting_room', 'price_per_30min' => 1250, 'capacity' => 12, 'equipment' => 'プロジェクター、ホワイトボード', 'amenities' => ['ホワイトボード'], 'description' => '大人数の会議に適した会議室です。', 'image_path' => 'images/facilities/umeda-large-meeting.jpg', 'is_active' => true],
            ['shop' => 'CoSpace 梅田', 'name' => 'サイレントエリア', 'type' => 'area', 'price_per_30min' => 150, 'capacity' => 8, 'equipment' => '私語・通話禁止', 'amenities' => [], 'description' => '設備点検のため現在利用できません。', 'image_path' => 'images/facilities/umeda-silent.jpg', 'is_active' => false],
        ];

        foreach ($facilities as $facility) {
            $shop = $shops->get($facility['shop']);
            unset($facility['shop']);

            Facility::query()->updateOrCreate(
                ['shop_id' => $shop->id, 'name' => $facility['name']],
                $facility,
            );
        }
    }
}
