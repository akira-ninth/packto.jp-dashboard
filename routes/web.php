<?php

use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use Illuminate\Support\Facades\Route;

$adminDomain = config('app.admin_domain');
$tenantDomain = config('app.app_domain');

/*
|--------------------------------------------------------------------------
| admin.packto.jp — master 用統合管理画面
|--------------------------------------------------------------------------
*/
Route::domain($adminDomain)
    ->middleware(['web', 'auth', 'role:master'])
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('customers', AdminCustomerController::class);
    });

/*
|--------------------------------------------------------------------------
| app.packto.jp — 顧客 (customer ロール) 用ダッシュボード
|--------------------------------------------------------------------------
*/
Route::domain($tenantDomain)
    ->middleware(['web', 'auth', 'role:customer'])
    ->name('tenant.')
    ->group(function (): void {
        Route::get('/', [TenantDashboardController::class, 'index'])->name('dashboard');
    });

/*
|--------------------------------------------------------------------------
| ドメイン非依存 — ログイン (両方のサブドメインで使う)
|--------------------------------------------------------------------------
*/
Route::get('/login', function () {
    return view('auth.login');
})->name('login');
