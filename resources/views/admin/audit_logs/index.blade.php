@extends('layouts.app')

@section('title', '監査ログ | Packto Console')

@section('nav')
    <nav>
        <a href="{{ route('admin.dashboard') }}">ダッシュボード</a>
        <a href="{{ route('admin.customers.index') }}">顧客一覧</a>
        <a href="{{ route('admin.masters.index') }}">マスター</a>
        <a href="{{ route('admin.audit-logs.index') }}">監査ログ</a>
    </nav>
@endsection

@section('content')
    <h1>監査ログ (直近 200 件)</h1>

    <div class="card" style="padding: 0;">
        <table>
            <thead>
                <tr>
                    <th style="width: 150px;">日時</th>
                    <th>操作</th>
                    <th>対象</th>
                    <th>実行者</th>
                    <th style="width: 120px;">IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td><code style="font-size: 11px;">{{ $log->created_at?->format('Y-m-d H:i:s') }}</code></td>
                        <td><code style="font-size: 12px; background: #f3f4f6; padding: 2px 6px; border-radius: 3px;">{{ $log->action }}</code></td>
                        <td>
                            @if ($log->target_label)
                                <strong>{{ $log->target_label }}</strong>
                                @if ($log->target_type)
                                    <span style="color: #6b7280; font-size: 11px;">({{ $log->target_type }} #{{ $log->target_id }})</span>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            {{ $log->actor_email ?? '(unknown)' }}
                            @if ($log->actor_role)
                                <span class="badge {{ $log->actor_role === 'master' ? 'pro' : 'basic' }}" style="font-size: 10px;">{{ $log->actor_role }}</span>
                            @endif
                        </td>
                        <td><code style="font-size: 10px; color: #6b7280;">{{ $log->ip_address ?? '—' }}</code></td>
                    </tr>
                    @if ($log->metadata)
                        <tr>
                            <td></td>
                            <td colspan="4" style="font-size: 11px; color: #6b7280; padding-bottom: 12px;">
                                <code style="background: #f9fafb; padding: 2px 6px; border-radius: 3px;">{{ json_encode($log->metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</code>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr><td colspan="5" style="text-align: center; padding: 24px; color: #6b7280;">まだログがありません</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
