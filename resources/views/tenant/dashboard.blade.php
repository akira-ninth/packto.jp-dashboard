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
            @php
                $totalReqs = (int) ($summary['reqs'] ?? 0);
                $outBytes = (float) ($summary['output_bytes'] ?? 0);
                $inBytes = (float) ($summary['input_bytes'] ?? 0);
                $ratio = $inBytes > 0 ? ($outBytes / $inBytes) * 100 : null;
                $saved = $inBytes - $outBytes;
            @endphp
            @if ($totalReqs > 0)
                <div style="display: flex; gap: 32px; flex-wrap: wrap;">
                    <div>
                        <div style="font-size: 12px; color: #6b7280;">リクエスト数</div>
                        <div style="font-size: 32px; font-weight: 700;">{{ number_format($totalReqs) }}</div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #6b7280;">配信 (output)</div>
                        <div style="font-size: 32px; font-weight: 700;">{{ number_format($outBytes / 1024 / 1024, 2) }}<span style="font-size: 14px; color: #6b7280;"> MB</span></div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #6b7280;">origin (input)</div>
                        <div style="font-size: 32px; font-weight: 700;">{{ number_format($inBytes / 1024 / 1024, 2) }}<span style="font-size: 14px; color: #6b7280;"> MB</span></div>
                    </div>
                    @if ($ratio !== null)
                        <div>
                            <div style="font-size: 12px; color: #6b7280;">圧縮後 / origin</div>
                            <div style="font-size: 32px; font-weight: 700; color: #059669;">{{ number_format($ratio, 1) }}<span style="font-size: 14px;">%</span></div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #6b7280;">削減量</div>
                            <div style="font-size: 32px; font-weight: 700; color: #059669;">{{ number_format($saved / 1024 / 1024, 2) }}<span style="font-size: 14px; color: #6b7280;"> MB</span></div>
                        </div>
                    @endif
                </div>
            @else
                <p style="color: #6b7280;">直近 7 日のリクエストがありません (Analytics Engine の集計待ちかも)</p>
            @endif
        </div>

        @if (! empty($byDay))
            <div class="card">
                <h2>日別</h2>
                <canvas id="dayChart" height="100"></canvas>
                <table style="margin-top: 16px;">
                    <thead><tr><th>日付</th><th style="text-align: right;">req</th><th style="text-align: right;">配信 MB</th><th style="text-align: right;">origin MB</th></tr></thead>
                    <tbody>
                        @foreach ($byDay as $row)
                            <tr>
                                <td><code>{{ $row['day'] }}</code></td>
                                <td style="text-align: right;">{{ number_format((int) $row['reqs']) }}</td>
                                <td style="text-align: right;">{{ number_format((float) $row['output_bytes'] / 1024 / 1024, 2) }}</td>
                                <td style="text-align: right;">{{ number_format((float) $row['input_bytes'] / 1024 / 1024, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if (! empty($byFormat))
            <div class="card">
                <h2>フォーマット別 (圧縮率)</h2>
                <table>
                    <thead><tr><th>format</th><th style="text-align: right;">req</th><th style="text-align: right;">origin MB</th><th style="text-align: right;">配信 MB</th><th style="text-align: right;">圧縮率</th></tr></thead>
                    <tbody>
                        @foreach ($byFormat as $row)
                            @php
                                $rIn = (float) ($row['input_bytes'] ?? 0);
                                $rOut = (float) ($row['output_bytes'] ?? 0);
                                $rRatio = $rIn > 0 ? ($rOut / $rIn) * 100 : null;
                            @endphp
                            <tr>
                                <td><code>{{ $row['format'] ?: '(none)' }}</code></td>
                                <td style="text-align: right;">{{ number_format((int) $row['reqs']) }}</td>
                                <td style="text-align: right;">{{ number_format($rIn / 1024 / 1024, 2) }}</td>
                                <td style="text-align: right;">{{ number_format($rOut / 1024 / 1024, 2) }}</td>
                                <td style="text-align: right; color: #059669; font-weight: 600;">
                                    {{ $rRatio !== null ? number_format($rRatio, 1).'%' : '—' }}
                                </td>
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
                    <thead><tr><th>cache_status</th><th style="text-align: right;">req</th></tr></thead>
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

        @if (! empty($byDay))
            {{-- Chart.js (CDN) で日別グラフを描画 --}}
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
            <script>
                (function () {
                    const rows = @json($byDay);
                    const labels = rows.map(r => r.day);
                    const reqs = rows.map(r => parseInt(r.reqs, 10));
                    const outMb = rows.map(r => (parseFloat(r.output_bytes) / 1024 / 1024).toFixed(2));
                    const inMb = rows.map(r => (parseFloat(r.input_bytes) / 1024 / 1024).toFixed(2));
                    const ctx = document.getElementById('dayChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                { label: 'origin MB', data: inMb, backgroundColor: 'rgba(251, 146, 60, 0.6)', yAxisID: 'y' },
                                { label: '配信 MB', data: outMb, backgroundColor: 'rgba(37, 99, 235, 0.7)', yAxisID: 'y' },
                                { label: 'req 数', data: reqs, type: 'line', borderColor: '#059669', backgroundColor: '#059669', yAxisID: 'y1', tension: 0.2 },
                            ],
                        },
                        options: {
                            responsive: true,
                            interaction: { mode: 'index', intersect: false },
                            scales: {
                                y: { type: 'linear', position: 'left', title: { display: true, text: 'MB' } },
                                y1: { type: 'linear', position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'req' } },
                            },
                        },
                    });
                })();
            </script>
        @endif
    @else
        <div class="card">
            <p>顧客情報が紐付いていません。マスターアカウント (admin) にお問い合わせください。</p>
        </div>
    @endif
@endsection
