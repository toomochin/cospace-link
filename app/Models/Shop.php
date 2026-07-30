<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'area_name', 'address', 'access', 'opening_hours',
        'description', 'image_path', 'amenities', 'is_active',
    ];

    protected function casts(): array
    {
        return ['amenities' => 'array', 'is_active' => 'boolean'];
    }

    public function facilities(): HasMany
    {
        return $this->hasMany(Facility::class);
    }

    public function owners(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'shop_owner');
    }
}
