<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FacilityController as AdminFacilityController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Admin\ShopController as AdminShopController;
use App\Http\Controllers\Admin\ToggleShopStatusController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Owner\FacilityController as OwnerFacilityController;
use App\Http\Controllers\Owner\ReservationController as OwnerReservationController;
use App\Http\Controllers\Owner\ShopController as OwnerShopController;
use App\Http\Controllers\PortalFacilityController;
use App\Http\Controllers\PortalReservationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Middleware\EnsureShopOwner;
use App\Http\Middleware\EnsureSystemAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', [PortalFacilityController::class, 'index'])->name('home');
Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');
Route::get('/facilities/{facility}', [PortalFacilityController::class, 'show'])->name('facilities.show');
Route::get('/email/verify', fn () => view('auth.verify-email'))->middleware('auth')->name('verification.notice');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/reservations/create', [PortalReservationController::class, 'create'])->name('reservations.create');
    Route::match(['get', 'post'], '/facilities/{id}/reservations/confirm', [PortalReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::post('/facilities/{id}/reservations', [PortalReservationController::class, 'store'])->name('reservations.store');
    Route::get('/my-reservations', [PortalReservationController::class, 'index'])->name('reservations.index');
    Route::delete('/my-reservations/{id}', [PortalReservationController::class, 'destroy'])->name('reservations.destroy');
    Route::get('/reservations/{id}/success', [PortalReservationController::class, 'success'])->name('reservations.success');
    Route::get('/reservations/{id}/cancel', [PortalReservationController::class, 'cancel'])->name('reservations.cancel');
});

Route::middleware(['auth', 'verified', EnsureShopOwner::class])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', OwnerDashboardController::class)->name('dashboard');
    Route::get('/shop', [OwnerShopController::class, 'edit'])->name('shop.edit');
    Route::put('/shop', [OwnerShopController::class, 'update'])->name('shop.update');
    Route::get('/facilities', [OwnerFacilityController::class, 'index'])->name('facilities.index');
    Route::get('/facilities/create', [OwnerFacilityController::class, 'create'])->name('facilities.create');
    Route::post('/facilities', [OwnerFacilityController::class, 'store'])->name('facilities.store');
    Route::get('/facilities/{facility}/edit', [OwnerFacilityController::class, 'edit'])->name('facilities.edit');
    Route::put('/facilities/{facility}', [OwnerFacilityController::class, 'update'])->name('facilities.update');
    Route::delete('/facilities/{facility}', [OwnerFacilityController::class, 'destroy'])->name('facilities.destroy');
    Route::get('/reservations', [OwnerReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/export', [OwnerReservationController::class, 'export'])->name('reservations.export');
});

Route::middleware(['auth', EnsureSystemAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/shops', [AdminShopController::class, 'index'])->name('shops.index');
    Route::get('/shops/create', [AdminShopController::class, 'create'])->name('shops.create');
    Route::post('/shops', [AdminShopController::class, 'store'])->name('shops.store');
    Route::get('/shops/{shop}/edit', [AdminShopController::class, 'edit'])->name('shops.edit');
    Route::put('/shops/{shop}', [AdminShopController::class, 'update'])->name('shops.update');
    Route::delete('/shops/{shop}', [AdminShopController::class, 'destroy'])->name('shops.destroy');
    Route::patch('/shops/{shop}/toggle-status', ToggleShopStatusController::class)->name('shops.toggle-status');
    Route::get('/facilities', [AdminFacilityController::class, 'index'])->name('facilities.index');
    Route::get('/facilities/create', [AdminFacilityController::class, 'create'])->name('facilities.create');
    Route::post('/facilities', [AdminFacilityController::class, 'store'])->name('facilities.store');
    Route::get('/facilities/{id}/edit', [AdminFacilityController::class, 'edit'])->name('facilities.edit');
    Route::put('/facilities/{id}', [AdminFacilityController::class, 'update'])->name('facilities.update');
    Route::get('/reservations', [AdminReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/export', [AdminReservationController::class, 'export'])->name('reservations.export');
    Route::get('/reservations/create', [AdminReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [AdminReservationController::class, 'store'])->name('reservations.store');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
});
