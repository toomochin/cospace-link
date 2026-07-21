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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('reservable'); // 会議室・エリア兼用のポリモーフィック関連
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('reserved_seats')->default(1);
            $table->string('status')->default('pending_payment'); // 決済待ちで作成
            $table->timestamps();

            // 重複チェックのSQLを高速化するためのインデックス
            $table->index(['start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
