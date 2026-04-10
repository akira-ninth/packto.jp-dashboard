<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\CustomerUserController as AdminCustomerUserController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MasterController as AdminMasterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\Tenant\SetupController as TenantSetupController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

$adminDomain = config('app.admin_domain');
$tenantDomain = config('app.app_domain');

/*
|--------------------------------------------------------------------------
| Phase 13k: 軽量ヘルスチェック (Laravel default /up を置き換え)
|--------------------------------------------------------------------------
| Laravel 13 default の /up は HTML レンダラで cdn.jsdelivr.net + fonts.bunny.net
| を読み込み、app 名も漏洩する。情報漏洩 + 外部依存を避けるため自前の最小レスポンスに置換。
*/
Route::get('/up', fn () => response()->json(['ok' => true]));

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

    // Phase 13m: パスワードリセット (セルフサービス)
    Route::get('/password/forgot', [PasswordResetController::class, 'showForgotForm'])->name('password.forgot');
    Route::post('/password/forgot', [PasswordResetController::class, 'sendResetLink']);
    Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [PasswordResetController::class, 'reset']);

    // Phase 13n: 2FA challenge (パスワード認証直後)
    Route::get('/two-factor/challenge', [LoginController::class, 'showTwoFactorChallenge'])->name('two-factor.challenge');
    Route::post('/two-factor/challenge', [LoginController::class, 'verifyTwoFactorChallenge']);
});

/*
|--------------------------------------------------------------------------
| Phase 13n: 2FA セットアップ (ログイン後)
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/two-factor', [TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/two-factor/setup', [TwoFactorController::class, 'setup'])->name('two-factor.setup');
    Route::post('/two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
    Route::post('/two-factor/disable', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
    Route::post('/two-factor/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('two-factor.recovery-codes');
});

/*
|--------------------------------------------------------------------------
| アカウント設定 (両ロール共通)
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::patch('/account/email', [AccountController::class, 'updateEmail'])->name('account.email.update');
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

        // Phase 13h: master アカウント管理
        Route::get('masters', [AdminMasterController::class, 'index'])->name('masters.index');
        Route::post('masters', [AdminMasterController::class, 'store'])->name('masters.store');
        Route::delete('masters/{master}', [AdminMasterController::class, 'destroy'])->name('masters.destroy');

        // Phase 13l: 監査ログ閲覧
        Route::get('audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
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
        Route::get('/setup', [TenantSetupController::class, 'guide'])->name('setup');
        Route::post('/setup/check', [TenantSetupController::class, 'check'])->name('setup.check');
    });
