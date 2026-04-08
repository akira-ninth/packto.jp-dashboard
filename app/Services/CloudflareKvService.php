<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Log;

/**
 * Cloudflare KV (CUSTOMERS namespace) への書き込みヘルパ。
 *
 * Cloudflare worker (imagycore-master) の resolveCustomer は env.CUSTOMERS から
 * 値を読む。packto-console は AdminCustomerController で顧客を CRUD した時に
 * このサービス経由で同じ KV に書き込む。
 *
 * 設計方針:
 * - **失敗してもアプリは止めない** (KV 書き込み失敗時は warn ログ + 続行)
 *   理由: KV エラーで顧客 CRUD が落ちると管理画面が使えなくなる。worker 側で
 *   hardcode フォールバックを残しているので、最悪 worker は動き続ける。
 * - **CLOUDFLARE_API_TOKEN 未設定なら no-op** (ローカル開発・テスト用)
 *
 * @see https://developers.cloudflare.com/api/operations/workers-kv-namespace-write-key-value-pair
 */
class CloudflareKvService
{
    public function __construct(
        private readonly HttpFactory $http,
    ) {}

    public function putCustomer(Customer $customer): bool
    {
        if (! $this->isConfigured()) {
            Log::warning('CloudflareKvService not configured — skipping put', ['subdomain' => $customer->subdomain]);
            return false;
        }

        $payload = [
            'origin' => $customer->origin_url,
            'plan' => $customer->plan->slug,
        ];

        $response = $this->http
            ->withToken(config('services.cloudflare.api_token'))
            ->withHeaders(['Content-Type' => 'application/json'])
            ->withBody(json_encode($payload, JSON_UNESCAPED_SLASHES), 'application/json')
            ->put($this->endpointForKey($customer->subdomain));

        if ($response->failed()) {
            Log::warning('Cloudflare KV put failed', [
                'subdomain' => $customer->subdomain,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        }

        return true;
    }

    public function deleteCustomer(string $subdomain): bool
    {
        if (! $this->isConfigured()) {
            Log::warning('CloudflareKvService not configured — skipping delete', ['subdomain' => $subdomain]);
            return false;
        }

        $response = $this->http
            ->withToken(config('services.cloudflare.api_token'))
            ->delete($this->endpointForKey($subdomain));

        if ($response->failed()) {
            Log::warning('Cloudflare KV delete failed', [
                'subdomain' => $subdomain,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        }

        return true;
    }

    public function isConfigured(): bool
    {
        return ! empty(config('services.cloudflare.api_token'))
            && ! empty(config('services.cloudflare.account_id'))
            && ! empty(config('services.cloudflare.kv_customers_namespace_id'));
    }

    private function endpointForKey(string $key): string
    {
        $accountId = config('services.cloudflare.account_id');
        $namespaceId = config('services.cloudflare.kv_customers_namespace_id');

        return "https://api.cloudflare.com/client/v4/accounts/{$accountId}/storage/kv/namespaces/{$namespaceId}/values/".rawurlencode($key);
    }
}
