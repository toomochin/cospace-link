<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\Admin\FacilityController as AdminFacilityController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Middleware\AdminMiddleware;

// 1. トップ画面（FacilityController経由で施設一覧を渡す）
Route::get('/', [FacilityController::class, 'index'])
    ->name('home');

// 2. メール認証待ち画面
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// 施設詳細
Route::get('/facilities/{id}', [FacilityController::class, 'show'])->name('facilities.show');

// 3. プロフィール関連（認証＆メール確認済みユーザーのみ）
Route::middleware(['auth', 'verified'])->group(function () {
    // 画面表示（GET）
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    // 更新処理（PUT）
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // 予約関連
    Route::post('/facilities/{id}/reservations/confirm', [ReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::post('/facilities/{id}/reservations', [ReservationController::class, 'store'])->name('reservations.store');

    // マイページ（予約一覧）とキャンセルのルート
    Route::get('/my-reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::delete('/my-reservations/{id}', [ReservationController::class, 'destroy'])->name('reservations.destroy');

    // Stripe 決済成功・キャンセル時のルート
    Route::get('/reservations/{id}/success', [ReservationController::class, 'success'])->name('reservations.success');
    Route::get('/reservations/{id}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
});

// 管理者専用ルート
Route::middleware(['auth', AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    // 施設管理
    Route::get('/facilities', [AdminFacilityController::class, 'index'])->name('facilities.index');
    Route::get('/facilities/create', [AdminFacilityController::class, 'create'])->name('facilities.create');
    Route::post('/facilities', [AdminFacilityController::class, 'store'])->name('facilities.store');
    Route::get('/facilities/{id}/edit', [AdminFacilityController::class, 'edit'])->name('facilities.edit');
    Route::put('/facilities/{id}', [AdminFacilityController::class, 'update'])->name('facilities.update');

    // 予約一覧確認
    Route::get('/reservations', [AdminReservationController::class, 'index'])->name('reservations.index');
});