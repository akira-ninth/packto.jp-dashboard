<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * テナント: セットアップガイド (タグ発行) + 動作チェック。
 */
class SetupController extends Controller
{
    /**
     * タグ発行画面 — .htaccess / HTML タグのコードスニペットを表示。
     */
    public function guide(Request $request): View
    {
        $customer = $request->user()->customer;
        $subdomain = $customer?->subdomain ?? 'example';
        $origin = $customer?->origin_url ?? 'https://example.com';

        return view('tenant.setup', [
            'customer' => $customer,
            'subdomain' => $subdomain,
            'origin' => $origin,
        ]);
    }

    /**
     * packto 有効チェック — 顧客サブドメインに HTTP リクエストを投げて
     * x-imagy-version ヘッダの有無で CDN 経由を確認する。
     */
    public function check(Request $request): JsonResponse
    {
        $customer = $request->user()->customer;
        if (! $customer) {
            return response()->json(['ok' => false, 'error' => '顧客情報が紐付いていません']);
        }

        $testUrl = "https://{$customer->subdomain}.packto.jp/favicon.ico";

        try {
            $response = Http::timeout(10)->withHeaders([
                'User-Agent' => 'Packto-StatusCheck/1.0',
            ])->get($testUrl);

            $version = $response->header('x-imagy-version');
            $status = $response->status();

            if ($version) {
                return response()->json([
                    'ok' => true,
                    'status' => $status,
                    'version' => $version,
                    'message' => "packto CDN は正常に動作しています (worker: {$version})",
                ]);
            }

            return response()->json([
                'ok' => false,
                'status' => $status,
                'message' => 'x-imagy-version ヘッダが見つかりません。.htaccess の設定を確認してください。',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Packto status check failed', [
                'subdomain' => $customer->subdomain,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'ok' => false,
                'message' => '接続エラー: '.class_basename($e).' — DNS やドメイン設定を確認してください。',
            ]);
        }
    }
}
