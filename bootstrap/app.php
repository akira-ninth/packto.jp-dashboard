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
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
