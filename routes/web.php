<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\ReservationController;

// 1. トップ画面（FacilityController経由で施設一覧を渡す）
Route::get('/', [FacilityController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('home');

// 2. メール認証待ち画面
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// 3. プロフィール関連（認証＆メール確認済みユーザーのみ）
Route::middleware(['auth', 'verified'])->group(function () {
    // 画面表示（GET）
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    // 更新処理（PUT）
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // 施設詳細
    Route::get('/facilities/{id}', [FacilityController::class, 'show'])->name('facilities.show');

    // 予約関連
    Route::post('/facilities/{id}/reservations/confirm', [ReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::post('/facilities/{id}/reservations', [ReservationController::class, 'store'])->name('reservations.store');
});