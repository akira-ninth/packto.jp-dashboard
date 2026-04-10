@extends('layouts.admin')

@section('title', '監査ログ | Packto Console')

@section('content')
    <h1 class="h4 fw-bold mb-4">監査ログ (直近 200 件)</h1>

    <div class="block block-rounded">
        <div class="block-content p-0">
            <table class="table table-hover table-vcenter mb-0">
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
                            <td><code style="font-size: .6875rem;">{{ $log->created_at?->format('Y-m-d H:i:s') }}</code></td>
                            <td><span class="badge bg-light text-dark" style="font-size: .75rem;">{{ $log->action }}</span></td>
                            <td>
                                @if ($log->target_label)
                                    <strong>{{ $log->target_label }}</strong>
                                    @if ($log->target_type)
                                        <span class="text-muted" style="font-size: .6875rem;">({{ $log->target_type }} #{{ $log->target_id }})</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                {{ $log->actor_email ?? '(unknown)' }}
                                @if ($log->actor_role)
                                    <span class="badge {{ $log->actor_role === 'master' ? 'badge-plan-pro' : 'badge-plan-basic' }}" style="font-size: .625rem;">{{ $log->actor_role }}</span>
                                @endif
                            </td>
                            <td><code class="text-muted" style="font-size: .625rem;">{{ $log->ip_address ?? '—' }}</code></td>
                        </tr>
                        @if ($log->metadata)
                            <tr>
                                <td></td>
                                <td colspan="4" style="font-size: .6875rem; padding-bottom: .75rem;">
                                    <code class="text-muted" style="background: #f9fafb; padding: 2px 6px; border-radius: 3px;">{{ json_encode($log->metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</code>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">まだログがありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
