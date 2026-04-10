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
    /**
     * packto 動作確認 — ユーザが入力した URL をサーバ側から取得して
     * packto CDN (subdomain.packto.jp) 経由になっているか確認する。
     *
     * 手順:
     * 1. 入力 URL を fetch → リダイレクト先が *.packto.jp かチェック
     * 2. リダイレクト先 (or 直接) に x-imagy-version ヘッダがあれば OK
     */
    public function check(Request $request): JsonResponse
    {
        $customer = $request->user()->customer;
        if (! $customer) {
            return response()->json(['ok' => false, 'message' => '顧客情報が紐付いていません']);
        }

        $data = $request->validate(['url' => ['required', 'url']]);
        $inputUrl = $data['url'];

        try {
            // まず origin URL を叩いてリダイレクトを確認 (follow しない)
            $originResp = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Packto-StatusCheck/1.0'])
                ->withOptions(['allow_redirects' => false])
                ->get($inputUrl);

            $location = $originResp->header('location');
            $isRedirected = $originResp->isRedirect() && $location && str_contains($location, '.packto.jp');

            if (! $isRedirected) {
                return response()->json([
                    'ok' => false,
                    'message' => "このURL は packto.jp へリダイレクトされていません (HTTP {$originResp->status()})。.htaccess が正しく設定されているか確認してください。",
                ]);
            }

            // packto.jp 経由の URL を叩いてヘッダ確認
            $packtoResp = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Packto-StatusCheck/1.0'])
                ->get($location);

            $version = $packtoResp->header('x-imagy-version');
            $history = $packtoResp->header('x-imagy-process-history');

            if ($version) {
                return response()->json([
                    'ok' => true,
                    'message' => "packto CDN 経由で配信されています。\nリダイレクト先: {$location}\nworker: {$version}" . ($history ? "\n処理: {$history}" : ''),
                ]);
            }

            return response()->json([
                'ok' => false,
                'message' => "リダイレクトは確認できましたが、x-imagy-version ヘッダがありません。\nリダイレクト先: {$location}\nworker が応答していない可能性があります。",
            ]);
        } catch (\Throwable $e) {
            Log::warning('Packto status check failed', [
                'url' => $inputUrl,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'ok' => false,
                'message' => '接続エラー: '.class_basename($e).' — URL が正しいか確認してください。',
            ]);
        }
    }
}
