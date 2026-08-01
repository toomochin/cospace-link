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
            ['shop' => 'CoSpace 渋谷', 'name' => '会議室B', 'type' => 'meeting_room', 'price_per_30min' => 600, 'capacity' => 4, 'equipment' => 'モニター、ホワイトボード', 'amenities' => ['モニター', 'ホワイトボード'], 'description' => '少人数の打ち合わせに適した会議室です。', 'image_path' => 'images/facilities/shibuya-meeting-b.jpg', 'is_active' => true],
            ['shop' => 'CoSpace 渋谷', 'name' => '会議室C', 'type' => 'meeting_room', 'price_per_30min' => 1000, 'capacity' => 8, 'equipment' => '大型モニター、ホワイトボード、Web会議用カメラ', 'amenities' => ['モニター', 'ホワイトボード', 'Web会議ブース可'], 'description' => 'チーム会議やオンライン会議に利用できます。', 'image_path' => 'images/facilities/shibuya-meeting-c.jpg', 'is_active' => true],
            ['shop' => 'CoSpace 渋谷', 'name' => '集中ブース1', 'type' => 'meeting_room', 'price_per_30min' => 250, 'capacity' => 1, 'equipment' => '防音、Web会議用ライト', 'amenities' => ['防音', 'Web会議ブース可'], 'description' => '通話やWeb会議に適した1名用ブースです。', 'image_path' => 'images/facilities/shibuya-focus-booth-1.jpg', 'is_active' => true],
            ['shop' => 'CoSpace 渋谷', 'name' => '集中ブース2', 'type' => 'meeting_room', 'price_per_30min' => 250, 'capacity' => 1, 'equipment' => '防音、モニター', 'amenities' => ['モニター', '防音', 'Web会議ブース可'], 'description' => '設備調整のため現在利用できません。', 'image_path' => 'images/facilities/shibuya-focus-booth-2.jpg', 'is_active' => false],
            ['shop' => 'CoSpace 渋谷', 'name' => 'オープンワークエリア', 'type' => 'area', 'price_per_30min' => 200, 'capacity' => 20, 'equipment' => 'デスク、チェア', 'amenities' => [], 'description' => '短時間の作業にも使いやすい開放的な共用エリアです。', 'image_path' => 'images/facilities/shibuya-open-area.jpg', 'is_active' => true],
            ['shop' => 'CoSpace 渋谷', 'name' => 'セミナールーム', 'type' => 'meeting_room', 'price_per_30min' => 1500, 'capacity' => 16, 'equipment' => '大型モニター、ホワイトボード、Web会議用カメラ', 'amenities' => ['モニター', 'ホワイトボード', 'Web会議ブース可'], 'description' => '研修やセミナー、複数拠点との会議に適した部屋です。', 'image_path' => 'images/facilities/shibuya-seminar-room.jpg', 'is_active' => true],
            ['shop' => 'CoSpace 梅田', 'name' => '小会議室', 'type' => 'meeting_room', 'price_per_30min' => 550, 'capacity' => 4, 'equipment' => 'モニター、ホワイトボード', 'amenities' => ['モニター', 'ホワイトボード'], 'description' => '少人数でのミーティング向けの個室です。', 'image_path' => 'images/facilities/umeda-small-meeting.jpg', 'is_active' => true],
            ['shop' => 'CoSpace 梅田', 'name' => '中会議室', 'type' => 'meeting_room', 'price_per_30min' => 900, 'capacity' => 8, 'equipment' => '大型モニター、ホワイトボード', 'amenities' => ['モニター', 'ホワイトボード'], 'description' => '社内会議や商談に使いやすい8名用会議室です。', 'image_path' => 'images/facilities/umeda-medium-meeting.jpg', 'is_active' => true],
            ['shop' => 'CoSpace 梅田', 'name' => 'Web会議ブース', 'type' => 'meeting_room', 'price_per_30min' => 300, 'capacity' => 1, 'equipment' => '防音、モニター、Web会議用ライト', 'amenities' => ['モニター', '防音', 'Web会議ブース可'], 'description' => 'オンライン会議や面接に適した防音ブースです。', 'image_path' => 'images/facilities/umeda-web-booth.jpg', 'is_active' => true],
            ['shop' => 'CoSpace 梅田', 'name' => 'フリーアドレスエリア', 'type' => 'area', 'price_per_30min' => 180, 'capacity' => 24, 'equipment' => 'デスク、チェア、共有モニター', 'amenities' => ['モニター'], 'description' => '席を自由に選べる広い共用ワークエリアです。', 'image_path' => 'images/facilities/umeda-free-address.jpg', 'is_active' => true],
            ['shop' => 'CoSpace 梅田', 'name' => 'プロジェクトルーム', 'type' => 'meeting_room', 'price_per_30min' => 1100, 'capacity' => 10, 'equipment' => '大型モニター、ホワイトボード、防音設備', 'amenities' => ['モニター', 'ホワイトボード', '防音'], 'description' => '設備メンテナンスのため現在利用できません。', 'image_path' => 'images/facilities/umeda-project-room.jpg', 'is_active' => false],
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
