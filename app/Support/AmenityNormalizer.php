<?php

namespace App\Support;

use Illuminate\Support\Str;

class AmenityNormalizer
{
    private const ALIASES = [
        'wifi' => 'Wi-Fi',
        'wi-fi' => 'Wi-Fi',
        'ｗｉｆｉ' => 'Wi-Fi',
        '電源' => '電源',
        'コンセント' => '電源',
        'web会議ブース可' => 'Web会議ブース可',
        'web会議ブース' => 'Web会議ブース可',
        'モニター' => 'モニター',
        'ディスプレイ' => 'モニター',
        'プロジェクター' => 'プロジェクター',
        'ホワイトボード' => 'ホワイトボード',
        'フリードリンク' => 'フリードリンク',
        '防音' => '防音',
    ];

    /** @return list<string> */
    public static function normalize(array $amenities): array
    {
        return collect($amenities)
            ->filter(fn (mixed $amenity): bool => is_string($amenity))
            ->map(fn (string $amenity): string => trim(Str::squish($amenity)))
            ->filter()
            ->map(fn (string $amenity): string => self::ALIASES[Str::lower($amenity)] ?? $amenity)
            ->unique()
            ->values()
            ->all();
    }
}
