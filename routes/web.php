<?php

/*
 * MenuQR — web routes.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\OptionController;
use App\Http\Controllers\Admin\OptionGroupController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\DemoLoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PublicInfoController;
use App\Http\Controllers\PublicMenuController;
use App\Http\Controllers\PublicOrderController;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\Staff\KitchenController;
use App\Http\Controllers\Staff\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])
    ->middleware('throttle:30,1')
    ->name('locale.switch');
Route::get('/offline', fn () => view('offline'))->name('offline');

Route::get('/about', [PublicInfoController::class, 'about'])->name('info.about');
Route::get('/contact', [PublicInfoController::class, 'contact'])->name('info.contact');
Route::get('/privacy', [PublicInfoController::class, 'privacy'])->name('info.privacy');
Route::get('/terms', [PublicInfoController::class, 'terms'])->name('info.terms');

Route::get('/manifest.webmanifest', [PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/sw.js', [PwaController::class, 'serviceWorker'])->name('pwa.service-worker');

/*
|--------------------------------------------------------------------------
| Public table menu (no login) — the QR token is the only credential
|--------------------------------------------------------------------------
*/
Route::prefix('m/{table:token}')->name('menu.')->group(function () {
    Route::get('/', [PublicMenuController::class, 'show'])->middleware('throttle:120,1')->name('show');
    Route::get('/cart', [CartController::class, 'show'])->name('cart');
    Route::post('/cart', [CartController::class, 'add'])->middleware('throttle:40,1')->name('cart.add');
    Route::patch('/cart/{line}', [CartController::class, 'update'])->middleware('throttle:60,1')->name('cart.update');
    Route::delete('/cart/{line}', [CartController::class, 'remove'])->middleware('throttle:60,1')->name('cart.remove');
    Route::delete('/cart', [CartController::class, 'clear'])->middleware('throttle:20,1')->name('cart.clear');
    Route::post('/orders', [PublicOrderController::class, 'store'])->middleware('throttle:12,1')->name('orders.store');
    Route::get('/orders/{order:number}', [PublicOrderController::class, 'show'])->middleware('throttle:60,1')->name('orders.show');
});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:20,1')->name('login.store');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');
Route::post('/logout-all', [AuthenticatedSessionController::class, 'destroyAll'])
    ->middleware(['auth', 'throttle:10,1'])->name('logout.all');

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:6,1')->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Demo access — 404 when DEMO_MODE=false
|--------------------------------------------------------------------------
*/
Route::post('/demo-login', DemoLoginController::class)
    ->middleware('throttle:10,1')
    ->name('demo.login');

/*
|--------------------------------------------------------------------------
| Staff area (cashier + kitchen + admin)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('staff')->name('staff.')->group(function () {
    Route::get('/kitchen', [KitchenController::class, 'index'])
        ->middleware('role:admin,kitchen')->name('kitchen.index');
    Route::get('/kitchen/feed', [KitchenController::class, 'feed'])
        ->middleware('role:admin,kitchen')->name('kitchen.feed');

    Route::get('/orders', [OrderController::class, 'index'])
        ->middleware('role:admin,cashier')->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])
        ->middleware('role:admin,cashier')->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])
        ->name('orders.status');
});

/*
|--------------------------------------------------------------------------
| Admin area
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::resource('categories', CategoryController::class)->except('show');

    Route::resource('items', MenuItemController::class)->except('show');
    Route::patch('/items/{item}/toggle', [MenuItemController::class, 'toggle'])->name('items.toggle');
    Route::get('/items/{item}/options', [OptionGroupController::class, 'index'])->name('items.options.index');
    Route::post('/items/{item}/option-groups', [OptionGroupController::class, 'store'])->name('option-groups.store');
    Route::put('/items/{item}/option-groups/{group}', [OptionGroupController::class, 'update'])->name('option-groups.update');
    Route::delete('/items/{item}/option-groups/{group}', [OptionGroupController::class, 'destroy'])->name('option-groups.destroy');
    Route::post('/items/{item}/option-groups/{group}/options', [OptionController::class, 'store'])->name('options.store');
    Route::put('/items/{item}/option-groups/{group}/options/{option}', [OptionController::class, 'update'])->name('options.update');
    Route::delete('/items/{item}/option-groups/{group}/options/{option}', [OptionController::class, 'destroy'])->name('options.destroy');

    Route::get('/tables/print', [TableController::class, 'print'])->name('tables.print');
    Route::get('/tables/{table}/qr', [TableController::class, 'qr'])->name('tables.qr');
    Route::post('/tables/{table}/regenerate', [TableController::class, 'regenerate'])->name('tables.regenerate');
    Route::resource('tables', TableController::class)->except('show');

    Route::resource('staff', StaffController::class)->except('show');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/activity', [ActivityController::class, 'index'])->name('activity.index');
});
