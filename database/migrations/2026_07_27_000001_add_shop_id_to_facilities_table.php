<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->foreignId('shop_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
        });

        if (DB::table('facilities')->whereNull('shop_id')->exists()) {
            $shopId = DB::table('shops')->insertGetId([
                'name' => 'CoSpace 既存店舗',
                'area_name' => '未設定',
                'address' => '未設定',
                'opening_hours' => '09:00-21:00',
                'description' => '移行前から登録されている施設の所属店舗です。',
                'amenities' => json_encode([], JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('facilities')->whereNull('shop_id')->update(['shop_id' => $shopId]);
        }
    }

    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shop_id');
        });
    }
};
