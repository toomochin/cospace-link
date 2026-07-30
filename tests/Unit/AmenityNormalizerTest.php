<?php

namespace Tests\Unit;

use App\Support\AmenityNormalizer;
use PHPUnit\Framework\TestCase;

class AmenityNormalizerTest extends TestCase
{
    public function test_it_normalizes_aliases_and_removes_duplicates(): void
    {
        $this->assertSame(
            ['Wi-Fi', '電源', 'Web会議ブース可'],
            AmenityNormalizer::normalize([' wifi ', 'Wi-Fi', 'コンセント', 'Web会議ブース']),
        );
    }
}
