<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Phase 13k: セキュリティヘッダ一括付与。
 *
 * Cloudflare proxied + Xserver bypass route の構成では、Cloudflare はヘッダを
 * 加工しないので Laravel 側で全部明示的に付与する。
 *
 * - HSTS: 1 年強制、サブドメイン (admin/app) にも適用
 * - X-Frame-Options: SAMEORIGIN (クリックジャッキング防止、自分サイト内 iframe は許可)
 * - X-Content-Type-Options: nosniff (MIME sniff 防止)
 * - Referrer-Policy: strict-origin-when-cross-origin (リファラ漏洩抑制)
 * - Permissions-Policy: 不要 API 全部閉じる
 * - Content-Security-Policy: 自分の origin + 必要な CDN (jsdelivr) のみ
 *
 * CSP の 'unsafe-inline' は現状 inline style と inline script を多用してるので
 * 必要。中長期で nonce 化 + ハッシュ化を検討。
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), interest-cohort=()');

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: https:",
            "font-src 'self' data:",
            "connect-src 'self'",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
