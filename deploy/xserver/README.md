# Xserver デプロイ手順

`packto-console` を Xserver の `packto.jp` に配置する手順。

## 前提

- Xserver で `packto.jp` のサブドメイン `admin.packto.jp` と `app.packto.jp` を作成済み
- 各 doc root が以下に固定されていること:
  - FTP ルート = `/`
  - admin: `/admin.packto.jp/`
  - app: `/app.packto.jp/`
- Xserver の PHP バージョンが **8.5.x** に設定されていること (Laravel 13 は PHP 8.2+)
- Composer は Xserver 上に入っていなくても OK (ローカルで `vendor/` を生成して同梱)

## 最終的な配置

```
/                              ← FTP ルート
├── packto-console/            ← ★ このリポジトリの中身を全部置く (vendor/ 含む)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/                ← 元の public/ (使わない、消さないこと)
│   ├── resources/
│   ├── routes/
│   ├── storage/               ← 書き込み権限 755
│   ├── vendor/                ← composer install --no-dev で生成
│   ├── .env                   ← .env.production をここに rename して配置
│   └── ...
│
├── admin.packto.jp/           ← Xserver 固定 doc root
│   ├── .htaccess              ← deploy/xserver/admin.packto.jp/.htaccess を置く
│   └── index.php              ← deploy/xserver/admin.packto.jp/index.php を置く
│
├── app.packto.jp/             ← Xserver 固定 doc root
│   ├── .htaccess              ← deploy/xserver/app.packto.jp/.htaccess を置く
│   └── index.php              ← deploy/xserver/app.packto.jp/index.php を置く
│
└── packto.jp/                 ← apex (LP / サービスサイト用、別物)
```

## 初回デプロイ手順

### 1. ローカルで本番ビルドを準備

```sh
cd /Users/akira/dev/packto-console

# 開発用パッケージを除いて vendor/ を生成
composer install --no-dev --optimize-autoloader

# 設定キャッシュ等を生成 (本番では必須)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. .env.production を .env として準備

`.env.production` を Xserver にアップロードする際は **`.env` という名前にリネーム** すること。
ファイル名が `.env.production` のままだと Laravel に読まれない。

`APP_KEY` がまだ空なら、ローカルで一時的にコピーして生成:

```sh
cp .env.production .env.tmp-prod
APP_ENV=production php -r "
\$lines = file('.env.tmp-prod');
\$key = 'base64:'.base64_encode(random_bytes(32));
foreach (\$lines as &\$line) {
    if (str_starts_with(\$line, 'APP_KEY=')) \$line = \"APP_KEY={\$key}\n\";
}
file_put_contents('.env.tmp-prod', implode('', \$lines));
echo \$key.PHP_EOL;
"
# 生成された APP_KEY をメモして .env.tmp-prod を Xserver に .env としてアップ
```

または Xserver にアップ後に SSH で `php artisan key:generate --force` でも OK。

### 3. ファイルを Xserver にアップロード

#### A. SSH + rsync (推奨、SSH 契約済みなら)

```sh
# Laravel 本体 (vendor/ 含む) を /packto-console/ へ
rsync -avz --delete \
  --exclude='.git/' \
  --exclude='node_modules/' \
  --exclude='.env' \
  --exclude='database/database.sqlite' \
  --exclude='deploy/' \
  --exclude='tests/' \
  --exclude='storage/logs/*' \
  /Users/akira/dev/packto-console/ \
  USER@SERVER:/home/USER/packto-console/

# .env.tmp-prod を .env としてアップ
scp /Users/akira/dev/packto-console/.env.tmp-prod \
  USER@SERVER:/home/USER/packto-console/.env

# 各 doc root に bootstrap ファイルを配置
rsync -av /Users/akira/dev/packto-console/deploy/xserver/admin.packto.jp/ \
  USER@SERVER:/home/USER/admin.packto.jp/

rsync -av /Users/akira/dev/packto-console/deploy/xserver/app.packto.jp/ \
  USER@SERVER:/home/USER/app.packto.jp/
```

#### B. FTP (FileZilla 等)

1. ローカルの `packto-console/` ディレクトリ全体を FTP ルートにアップロード
   (`vendor/`, `bootstrap/`, `config/` 等を含む。`.git/`, `node_modules/`, `database/database.sqlite`, `tests/`, `deploy/` は除外)
2. ローカルの `.env.production` (or `.env.tmp-prod`) を `/packto-console/.env` としてアップロード
3. ローカルの `deploy/xserver/admin.packto.jp/index.php` と `.htaccess` を Xserver の `/admin.packto.jp/` にアップロード
4. ローカルの `deploy/xserver/app.packto.jp/index.php` と `.htaccess` を Xserver の `/app.packto.jp/` にアップロード

### 4. Xserver 上で初期化

#### SSH 経由

```sh
ssh USER@SERVER
cd /home/USER/packto-console

# APP_KEY 未設定なら
php artisan key:generate --force

# DB マイグレーション + シード (初回のみ)
php artisan migrate --force --seed

# storage/ と bootstrap/cache/ に書き込み権限
chmod -R 755 storage bootstrap/cache
```

#### SSH 無し (FTP のみ)

`packto-console/public_init.php` のような一時 web スクリプトを doc root に置いて 1 回叩く方法もあるが、
**Xserver は SSH を契約してれば使う方が圧倒的に楽** なので SSH 推奨。

### 5. 動作確認

```sh
# admin に master でログインできるか
curl -i https://admin.packto.jp/

# app に customer でログインできるか
curl -i https://app.packto.jp/

# Cloudflare Workers の bypass route が効いているか確認
# (admin/app は worker をスキップして Xserver に直接届く)
curl -sI https://admin.packto.jp/ | grep -i 'x-imagy'
# → x-imagy-* ヘッダが無ければ正解 (worker 通ってない)
```

## 2 回目以降のデプロイ (アプリ更新時)

```sh
# ローカルで
cd /Users/akira/dev/packto-console
composer install --no-dev --optimize-autoloader
php artisan config:cache && php artisan route:cache && php artisan view:cache

# Xserver の packto-console/ だけを更新 (doc root は触らない)
rsync -avz --delete \
  --exclude='.git/' --exclude='node_modules/' --exclude='.env' \
  --exclude='database/database.sqlite' --exclude='deploy/' \
  --exclude='tests/' --exclude='storage/logs/*' \
  /Users/akira/dev/packto-console/ \
  USER@SERVER:/home/USER/packto-console/

# Xserver 上で
ssh USER@SERVER
cd /home/USER/packto-console
php artisan migrate --force      # マイグレーションがあれば
php artisan config:cache         # .env を変えたら再キャッシュ
php artisan view:clear           # view cache をクリア
```

## トラブルシュート

### 500 エラー

1. `packto-console/storage/logs/laravel.log` を確認
2. `storage/` と `bootstrap/cache/` の権限を確認 (755)
3. `.env` が `/packto-console/.env` に存在するか確認 (`.env.production` のままになっていないか)
4. `APP_KEY` が空になっていないか
5. `php artisan config:clear` で設定キャッシュをクリアして再試行

### ルーティングが効かない (admin.packto.jp で何も出ない)

1. `/admin.packto.jp/index.php` が存在し、`packto-console/` を正しく参照しているか
2. `/admin.packto.jp/.htaccess` が存在し、`mod_rewrite` が効いているか
3. `php artisan route:list` で `admin.packto.jp/...` のルートが出るか
4. `.env` の `ADMIN_DOMAIN` が `admin.packto.jp` (デフォルト) になっているか

### `Class not found`

1. `vendor/` ディレクトリが正しくアップロードされているか
2. `composer install --no-dev --optimize-autoloader` をローカルで実行してから rsync したか

### Cloudflare 経由で 525/526 エラー

Cloudflare の SSL/TLS モードが "Full" 以上で、Xserver 側に有効な証明書 (Let's Encrypt) がインストールされている必要がある。
両方確認すること。

## セキュリティチェックリスト

- [ ] `/packto-console/.env` の DB_PASSWORD が強い (シード時の `changeme` を変えた)
- [ ] master / customer のデフォルトパスワードを変更した
- [ ] `APP_DEBUG=false` になっている
- [ ] `APP_ENV=production` になっている
- [ ] `/packto-console/.env` が web からアクセスできないことを確認 (`curl https://admin.packto.jp/../packto-console/.env` で 404 or 403)
- [ ] `storage/` `bootstrap/cache/` の権限が 755 (777 になっていない)
