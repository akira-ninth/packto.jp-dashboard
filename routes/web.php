<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\CustomerUserController as AdminCustomerUserController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use Illuminate\Support\Facades\Route;

$adminDomain = config('app.admin_domain');
$tenantDomain = config('app.app_domain');

/*
|--------------------------------------------------------------------------
| 認証 (両サブドメインで使う)
|--------------------------------------------------------------------------
| /login と /logout はドメイン非依存。session cookie の SESSION_DOMAIN=.packto.jp
| で admin/app 間で同一セッションを共有する。
*/
Route::middleware('web')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| アカウント設定 (両ロール共通)
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::patch('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');
});

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

        // Phase 13i: 顧客に紐付いた customer ロールユーザの追加 / 削除
        Route::post('customers/{customer}/users', [AdminCustomerUserController::class, 'store'])
            ->name('customers.users.store');
        Route::delete('customers/{customer}/users/{user}', [AdminCustomerUserController::class, 'destroy'])
            ->name('customers.users.destroy');
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
