<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 施設名
            $table->string('type'); // 種別 (meeting_room / area)
            $table->integer('price_per_30min'); // 30分あたりの価格
            $table->integer('capacity'); // 定員
            $table->string('equipment')->nullable(); // 設備・備品
            $table->text('description')->nullable(); // 説明文
            $table->string('image_path')->nullable(); // 画像ファイルパスを追加
            $table->boolean('is_active')->default(true); // 有効フラグ
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};