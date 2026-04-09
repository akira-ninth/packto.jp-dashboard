<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

/**
 * Phase 13l: 監査ログ記録ヘルパ。
 *
 * 全 master 操作 (顧客 CRUD / ユーザ CRUD / プラン変更 / パスワード変更 / 認証イベント) を
 * audit_logs テーブルに記録する。失敗してもアプリ動作は止めない。
 */
class AuditLogger
{
    /**
     * 操作を記録する。
     *
     * @param  string  $action  ドット区切り (例: 'customer.create', 'master.delete')
     * @param  array{type?:string,id?:int|string,label?:string}  $target
     * @param  array<string,mixed>  $metadata 任意の付加情報
     */
    public static function record(string $action, array $target = [], array $metadata = []): void
    {
        try {
            $actor = Auth::user();
            AuditLog::create([
                'actor_user_id' => $actor?->id,
                'actor_email' => $actor?->email,
                'actor_role' => $actor?->role,
                'action' => $action,
                'target_type' => $target['type'] ?? null,
                'target_id' => $target['id'] ?? null,
                'target_label' => $target['label'] ?? null,
                'metadata' => $metadata ?: null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // 監査ログ書き込み失敗はアプリを止めない (warn のみ)
            Log::warning('AuditLogger failed', ['action' => $action, 'error' => $e->getMessage()]);
        }
    }
}
