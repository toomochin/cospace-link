<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            // 会議室タイプ
            [
                'name' => '会議室A',
                'type' => 'meeting_room',
                'price_per_30min' => 750,
                'capacity' => 6,
                'equipment' => 'ホワイトボード、大型モニター',
                'description' => 'ホワイトボード、大型モニター完備のMTG用個室',
                'is_active' => true,
            ],
            [
                'name' => '会議室B',
                'type' => 'meeting_room',
                'price_per_30min' => 1250,
                'capacity' => 12,
                'equipment' => 'プロジェクター',
                'description' => 'プロジェクター完備の大人数向けセミナー・会議室',
                'is_active' => true,
            ],
            [
                'name' => '防音個室ブースA',
                'type' => 'meeting_room',
                'price_per_30min' => 250,
                'capacity' => 1,
                'equipment' => '完全防音',
                'description' => 'Web会議や面接に最適な完全防音のブース',
                'is_active' => true,
            ],
            [
                'name' => '防音個室ブースB',
                'type' => 'meeting_room',
                'price_per_30min' => 250,
                'capacity' => 1,
                'equipment' => '電源、高速Wi-Fi',
                'description' => '電源、高速Wi-Fi完備のWeb会議用個室',
                'is_active' => true,
            ],

            // エリアタイプ
            [
                'name' => 'フリーアドレス席（窓際カウンター）',
                'type' => 'area',
                'price_per_30min' => 100,
                'capacity' => 10,
                'equipment' => '電源',
                'description' => '外の景色が見える、電源完備の明るいカウンター席',
                'is_active' => true,
            ],
            [
                'name' => 'フリーアドレス席（中央大テーブル）',
                'type' => 'area',
                'price_per_30min' => 100,
                'capacity' => 16,
                'equipment' => 'マルチディスプレイ対応',
                'description' => '広々と作業できるマルチディスプレイ対応のメインテーブル',
                'is_active' => true,
            ],
            [
                'name' => 'サイレントエリア',
                'type' => 'area',
                'price_per_30min' => 150,
                'capacity' => 8,
                'equipment' => '私語・通話禁止',
                'description' => '私語・通話禁止の、集中して作業したい方向け静穏エリア',
                'is_active' => true,
            ],
            [
                'name' => 'リラックスソファ席',
                'type' => 'area',
                'price_per_30min' => 250,
                'capacity' => 6,
                'equipment' => '1人掛けソファ',
                'description' => '読書やアイデア出しに最適な、ゆったりとした1人掛けソファ',
                'is_active' => true,
            ],
            [
                'name' => 'ペアワーク',
                'type' => 'area',
                'price_per_30min' => 200,
                'capacity' => 8,
                'equipment' => 'L字型デスク（4組分）',
                'description' => '2人で共同作業やペアプログラミングに最適なL字型デスク（4組分）',
                'is_active' => true,
            ],
            [
                'name' => '集中ブース（半個室）',
                'type' => 'area',
                'price_per_30min' => 200,
                'capacity' => 5,
                'equipment' => 'パーテーション',
                'description' => '左右にパーティションがあり、周りの視線が気にならない半個室デスク',
                'is_active' => true,
            ],
        ];

        foreach ($facilities as $facility) {
            Facility::create($facility);
        }
    }
}