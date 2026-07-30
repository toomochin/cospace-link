<?php

namespace App\Support;

use Carbon\CarbonInterface;

class OpeningHours
{
    public static function contains(string $openingHours, CarbonInterface $startAt, CarbonInterface $endAt): bool
    {
        if (! preg_match('/^(\d{2}):(\d{2})-(\d{2}):(\d{2})$/', $openingHours, $matches)) {
            return false;
        }

        $opensAt = $startAt->copy()->setTime((int) $matches[1], (int) $matches[2]);
        $closesAt = $startAt->copy()->setTime((int) $matches[3], (int) $matches[4]);

        return $startAt->greaterThanOrEqualTo($opensAt)
            && $endAt->lessThanOrEqualTo($closesAt)
            && $startAt->isSameDay($endAt);
    }
}
