# packto-console

`packto.jp` の管理コンソール (Laravel 13 + MySQL)。

## 構成

- **admin.packto.jp** — master 用統合管理画面 (顧客追加・プラン変更・利用状況閲覧)
- **app.packto.jp** — 顧客向けダッシュボード (自分の使用量・設定)
- 1 つの Laravel アプリで `Route::domain()` を使ってサブドメイン分離
- ロール: `master` (admin にログイン可) / `customer` (app にログイン可)

姉妹プロジェクト: `imagycore-master` (Cloudflare Workers の CDN edge / `*.packto.jp` を処理)

## 環境

- PHP 8.5.4
- Composer 2.9.5
- Laravel 13.4.0
- 本番 DB: Xserver MySQL (`rayshd_packto`)
- ローカル開発 DB: SQLite (`database/database.sqlite`)

## ローカル開発

```bash
cd packto-console
composer install
php artisan migrate:fresh --seed
php artisan serve --port=8000
```

サブドメインルーティングをローカルでテストするには `/etc/hosts` に追記:

```
127.0.0.1 admin.packto.test
127.0.0.1 app.packto.test
```

`.env` で:

```
ADMIN_DOMAIN=admin.packto.test:8000
APP_DOMAIN=app.packto.test:8000
```

その後 `http://admin.packto.test:8000/` にブラウザでアクセス。

## デプロイ (Xserver)

`.env.production` を Xserver 上の `.env` として配置し、`php artisan migrate --force --seed` を 1 回実行。
admin.packto.jp と app.packto.jp の Apache ドキュメントルートを両方とも `packto-console/public/` に向ける。

## デフォルトユーザ (seed 後)

| メール | パスワード | ロール |
|---|---|---|
| `master@packto.jp` | `changeme` | master |
| `rays-hd@packto.jp` | `changeme` | customer (rays-hd) |

**本番デプロイ前に必ず変更すること。**

## Cloudflare worker 側との連携

`PLAN_FEATURES` (`database/seeders/PlanSeeder.php`) と `CUSTOMER_ORIGINS` (`database/seeders/CustomerSeeder.php`) は
`imagycore-master/cloudflare/src/shared/plans.js` および `origin.js` と同期する必要がある。

Phase 11 で resolveCustomer を Cloudflare KV から読む形に切り替え、ここから KV へ書き込む形になる予定。
