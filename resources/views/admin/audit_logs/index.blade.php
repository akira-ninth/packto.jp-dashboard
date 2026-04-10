@extends('layouts.admin')

@section('title', '監査ログ | Packto Console')

@section('content')
    <h4 class="c-grey-900 mT-10 mB-30">監査ログ (直近 200 件)</h4>

    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <table class="table">
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
                                    <span class="c-grey-600" style="font-size: .6875rem;">({{ $log->target_type }} #{{ $log->target_id }})</span>
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
                        <td><code class="c-grey-600" style="font-size: .625rem;">{{ $log->ip_address ?? '—' }}</code></td>
                    </tr>
                    @if ($log->metadata)
                        <tr>
                            <td></td>
                            <td colspan="4" style="font-size: .6875rem; padding-bottom: .75rem;">
                                <code class="c-grey-600 bgc-grey-100 pX-5 pY-5 bdrs-3">{{ json_encode($log->metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</code>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="5" class="ta-c c-grey-600 pY-20">まだログがありません</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
