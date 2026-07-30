<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    protected $fillable = [
        'user_id',
        'reservable_id',
        'reservable_type',
        'start_time',
        'end_time',
        'reserved_seats',
        'price',
        'payment_type',
        'status',
    ];

    /**
     * ポリモーフィックリレーション（Facility等）
     */
    public function reservable()
    {
        return $this->morphTo();
    }

    /**
     * 予約したユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
