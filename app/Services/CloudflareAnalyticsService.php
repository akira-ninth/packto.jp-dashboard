<?php

namespace App\Services;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Log;

/**
 * Cloudflare Analytics Engine SQL API クライアント。
 *
 * imagy worker (Phase 12a) が writeDataPoint で書いたイベントを SELECT で読む。
 * dataset 名 'imagy_requests' は wrangler.toml と一致させること。
 *
 * カラム mapping:
 *   index1   = customer サブドメイン
 *   blob1    = kind ('image' / 'text')
 *   blob2    = format ('avif', 'webp', 'js', 'css', ...)
 *   blob3    = HTTP status code (string)
 *   blob4    = cacheStatus ('HIT-VALIDATED' / 'CACHE-STALE' / 'MISS' / 'NONE')
 *   double1  = output bytes
 *   double2  = input bytes (Phase 12c で追加、圧縮率の分母)
 *
 * 設計方針:
 * - **クエリ失敗時はリクエストを落とさず空配列を返す** (使用量グラフはあくまで補助)
 * - CLOUDFLARE_API_TOKEN 未設定なら空配列 (ローカル開発時)
 *
 * @see https://developers.cloudflare.com/analytics/analytics-engine/sql-api/
 */
class CloudflareAnalyticsService
{
    public function __construct(
        private readonly HttpFactory $http,
    ) {}

    /**
     * 直近 N 日の顧客別サマリ ([{ reqs, output_bytes, input_bytes }])
     */
    public function getCustomerSummary(string $subdomain, int $days = 7): array
    {
        $sql = sprintf(
            "SELECT count() AS reqs, sum(double1) AS output_bytes, sum(double2) AS input_bytes "
            . "FROM imagy_requests "
            . "WHERE index1 = '%s' AND timestamp > NOW() - INTERVAL '%d' DAY",
            addslashes($subdomain),
            max(1, (int) $days),
        );

        $rows = $this->query($sql);
        return $rows[0] ?? ['reqs' => '0', 'output_bytes' => 0, 'input_bytes' => 0];
    }

    /**
     * 顧客の日別グラフ用 ([{ day, reqs, output_bytes, input_bytes }])
     */
    public function getCustomerByDay(string $subdomain, int $days = 7): array
    {
        $sql = sprintf(
            "SELECT toDate(timestamp) AS day, count() AS reqs, "
            . "sum(double1) AS output_bytes, sum(double2) AS input_bytes "
            . "FROM imagy_requests WHERE index1 = '%s' AND timestamp > NOW() - INTERVAL '%d' DAY "
            . "GROUP BY day ORDER BY day ASC",
            addslashes($subdomain),
            max(1, (int) $days),
        );

        return $this->query($sql);
    }

    /**
     * 顧客のフォーマット別 ([{ format, reqs, output_bytes, input_bytes }])
     * Phase 12c 以降は呼び出し側で input_bytes / output_bytes から圧縮率を計算する。
     */
    public function getCustomerByFormat(string $subdomain, int $days = 7): array
    {
        $sql = sprintf(
            "SELECT blob2 AS format, count() AS reqs, "
            . "sum(double1) AS output_bytes, sum(double2) AS input_bytes "
            . "FROM imagy_requests WHERE index1 = '%s' AND timestamp > NOW() - INTERVAL '%d' DAY "
            . "GROUP BY format ORDER BY reqs DESC",
            addslashes($subdomain),
            max(1, (int) $days),
        );

        return $this->query($sql);
    }

    /**
     * 顧客のキャッシュ状態別 ([{ cache_status, reqs }])
     */
    public function getCustomerByCacheStatus(string $subdomain, int $days = 7): array
    {
        $sql = sprintf(
            "SELECT blob4 AS cache_status, count() AS reqs "
            . "FROM imagy_requests WHERE index1 = '%s' AND timestamp > NOW() - INTERVAL '%d' DAY "
            . "GROUP BY cache_status ORDER BY reqs DESC",
            addslashes($subdomain),
            max(1, (int) $days),
        );

        return $this->query($sql);
    }

    /**
     * 全顧客のサマリを 1 回の SQL で取得 (index1 = subdomain で GROUP BY)
     * 顧客一覧ページでヒット数を並べるために使う。
     *
     * @return array<string, array{reqs: string, output_bytes: float, input_bytes: float}>
     */
    public function getAllCustomersSummary(int $days = 30): array
    {
        $sql = sprintf(
            "SELECT index1 AS subdomain, count() AS reqs, "
            . "sum(double1) AS output_bytes, sum(double2) AS input_bytes "
            . "FROM imagy_requests WHERE timestamp > NOW() - INTERVAL '%d' DAY "
            . "GROUP BY subdomain ORDER BY reqs DESC",
            max(1, (int) $days),
        );

        $rows = $this->query($sql);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['subdomain'] ?? ''] = $row;
        }
        return $result;
    }

    public function isConfigured(): bool
    {
        return ! empty(config('services.cloudflare.api_token'))
            && ! empty(config('services.cloudflare.account_id'));
    }

    /**
     * AE SQL を実行して data 配列を返す。失敗時は空配列。
     */
    private function query(string $sql): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        $accountId = config('services.cloudflare.account_id');
        $url = "https://api.cloudflare.com/client/v4/accounts/{$accountId}/analytics_engine/sql";

        $response = $this->http
            ->withToken(config('services.cloudflare.api_token'))
            ->withBody($sql, 'text/plain')
            ->post($url);

        if ($response->failed()) {
            Log::warning('AE SQL query failed', [
                'status' => $response->status(),
                'sql' => $sql,
                'body' => $response->body(),
            ]);
            return [];
        }

        $json = $response->json();
        return $json['data'] ?? [];
    }
}
