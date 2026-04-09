<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        // Phase 13k: Laravel default の /up は外部 CDN (jsdelivr/bunny.net) を読む
        // HTML レンダラなので、自前の routes/web.php で軽量 JSON に置換
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserRole::class,
        ]);

        // Phase 13k: 全レスポンスにセキュリティヘッダを付与 (HSTS / CSP / X-Frame-Options 等)
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Phase 13k: Cloudflare → Xserver 経由で実クライアント IP を取得するため
        // すべての proxy を trust する。Xserver Apache に届くのは Cloudflare edge のみで
        // クライアントから直接の X-Forwarded-For を受け付けない構成なので '*' で安全。
        $middleware->trustProxies(at: '*', headers:
            \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR
            | \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST
            | \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT
            | \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO
            | \Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
