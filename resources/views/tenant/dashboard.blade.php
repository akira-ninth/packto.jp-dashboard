@extends('layouts.app')

@section('title', 'ダッシュボード | Packto')

@section('nav')
    <nav>
        <a href="{{ route('tenant.dashboard') }}">ダッシュボード</a>
    </nav>
@endsection

@section('content')
    <h1>ダッシュボード</h1>

    @if ($customer)
        <div class="card">
            <h2>{{ $customer->display_name }}</h2>
            <table>
                <tr><th style="width: 180px;">配信ドメイン</th><td><code>{{ $customer->subdomain }}.packto.jp</code></td></tr>
                <tr><th>Origin</th><td>{{ $customer->origin_url }}</td></tr>
                <tr><th>プラン</th><td><span class="badge {{ $customer->plan->slug }}">{{ $customer->plan->name }}</span></td></tr>
                <tr><th>状態</th><td>
                    @if ($customer->active)
                        <span class="badge active">配信中</span>
                    @else
                        <span class="badge inactive">停止中</span>
                    @endif
                </td></tr>
            </table>
        </div>

        <div class="card">
            <h2>使用量サマリ (直近 7 日)</h2>
            @if ($summary && (int) $summary['reqs'] > 0)
                <div style="display: flex; gap: 32px;">
                    <div>
                        <div style="font-size: 12px; color: #6b7280;">リクエスト数</div>
                        <div style="font-size: 32px; font-weight: 700;">{{ number_format((int) $summary['reqs']) }}</div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #6b7280;">配信バイト数 (output)</div>
                        <div style="font-size: 32px; font-weight: 700;">{{ number_format((float) $summary['total_bytes'] / 1024 / 1024, 2) }}<span style="font-size: 14px; color: #6b7280;"> MB</span></div>
                    </div>
                </div>
            @else
                <p style="color: #6b7280;">直近 7 日のリクエストがありません (もしくは Analytics Engine の集計待ち)</p>
            @endif
        </div>

        @if (! empty($byDay))
            <div class="card">
                <h2>日別</h2>
                <table>
                    <thead><tr><th>日付</th><th style="text-align: right;">リクエスト数</th><th style="text-align: right;">配信 MB</th></tr></thead>
                    <tbody>
                        @foreach ($byDay as $row)
                            <tr>
                                <td><code>{{ $row['day'] }}</code></td>
                                <td style="text-align: right;">{{ number_format((int) $row['reqs']) }}</td>
                                <td style="text-align: right;">{{ number_format((float) $row['total_bytes'] / 1024 / 1024, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if (! empty($byFormat))
            <div class="card">
                <h2>フォーマット別</h2>
                <table>
                    <thead><tr><th>format</th><th style="text-align: right;">リクエスト数</th><th style="text-align: right;">配信 MB</th></tr></thead>
                    <tbody>
                        @foreach ($byFormat as $row)
                            <tr>
                                <td><code>{{ $row['format'] ?: '(none)' }}</code></td>
                                <td style="text-align: right;">{{ number_format((int) $row['reqs']) }}</td>
                                <td style="text-align: right;">{{ number_format((float) $row['total_bytes'] / 1024 / 1024, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if (! empty($byCache))
            <div class="card">
                <h2>キャッシュ状態別</h2>
                <table>
                    <thead><tr><th>cache_status</th><th style="text-align: right;">リクエスト数</th></tr></thead>
                    <tbody>
                        @foreach ($byCache as $row)
                            <tr>
                                <td><code>{{ $row['cache_status'] ?: '(none)' }}</code></td>
                                <td style="text-align: right;">{{ number_format((int) $row['reqs']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @else
        <div class="card">
            <p>顧客情報が紐付いていません。マスターアカウント (admin) にお問い合わせください。</p>
        </div>
    @endif
@endsection
