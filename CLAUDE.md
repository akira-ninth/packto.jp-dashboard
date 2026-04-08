# packto-console

## Commands

- Test: `php artisan test`
- Local serve: `php artisan serve --port=8000`
- Migrate fresh + seed: `php artisan migrate:fresh --seed`
- Route list: `php artisan route:list`

## Rules

- 既存のパターン・命名規則に従う。新しいパターンを発明しない
- 不要なファイル・ヘルパー・抽象化を生成しない
- console.log / dbg! / print デバッグを残さない
- `any` 型 (TS) / `unwrap()` (Rust) / bare `except` (Python) を避ける
- エラーハンドリングは既存コードの慣習に合わせる
- Laravel 13 の attribute 形式 (`#[Fillable(['...'])]`) を使う。古い `protected $fillable` は使わない

## Routing

- Tests: `tests/`
- Controllers: `app/Http/Controllers/{Admin,Tenant}/`
  - `Admin/` → master 用 (admin.packto.jp)
  - `Tenant/` → 顧客用 (app.packto.jp、URL は app だが namespace は Tenant)
- Models: `app/Models/`
- Views: `resources/views/{admin,tenant,layouts,auth}/`
- Migrations: `database/migrations/`
- Seeders: `database/seeders/`
- Domain routing: `routes/web.php` で `Route::domain(config('app.admin_domain'))` `Route::domain(config('app.app_domain'))`

## Project Notes

- 1 Laravel アプリで `admin.packto.jp` と `app.packto.jp` の 2 ドメインを `Route::domain()` で分離
- ロール (`users.role`): `master` → admin、`customer` → app、`EnsureUserRole` middleware (`role:master|customer`) で制御
- DB: ローカル SQLite、本番 Xserver MySQL (`rayshd_packto`)。FK 制約があるので migration の順序に注意 (plans → customers の順)
- Cloudflare worker (`imagycore-master`) 側の `PLAN_FEATURES` と `CUSTOMER_ORIGINS` は seeder と二重管理。Phase 11 で KV 化して一本化予定
