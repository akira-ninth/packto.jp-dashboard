<?php

/*
 * Xserver の admin.packto.jp ドキュメントルート用ブートストラップ
 *
 * Xserver の仕様で /admin.packto.jp/ がこのサブドメインの doc root になっている。
 * Laravel 本体は doc root の外 (/packto-console/) に置いて、ここから 1 つ上の階層を
 * 経由して参照する。
 *
 * 注意: app.packto.jp/index.php と完全に同一の内容。Laravel が Host ヘッダを見て
 * Route::domain() で分岐するので、両 doc root が同じ Laravel app を呼べばよい。
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../packto-console/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../packto-console/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../packto-console/bootstrap/app.php';

$app->handleRequest(Request::capture());
