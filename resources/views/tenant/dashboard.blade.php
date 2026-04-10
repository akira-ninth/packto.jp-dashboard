@extends('layouts.tenant')

@section('title', 'ダッシュボード | Packto')

@section('content')
    @if ($customer)
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4 mb-0 fw-bold">{{ $customer->display_name }}</h1>
            @if ($customer->active)
                <span class="badge badge-active"><i class="bi bi-check-circle me-1"></i>配信中</span>
            @else
                <span class="badge badge-inactive"><i class="bi bi-x-circle me-1"></i>停止中</span>
            @endif
        </div>

        {{-- Info tiles --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-tile">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary me-3"><i class="bi bi-globe2"></i></div>
                        <div>
                            <div class="stat-value" style="font-size: 16px;">{{ $customer->subdomain }}.packto.jp</div>
                            <div class="stat-label">配信ドメイン</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-tile">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-secondary me-3"><i class="bi bi-link-45deg"></i></div>
                        <div>
                            <div class="stat-value" style="font-size: 14px;">{{ $customer->origin_url }}</div>
                            <div class="stat-label">Origin</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-tile">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background:#6d28d9;" class="me-3"><i class="bi bi-stars"></i></div>
                        <div>
                            <div class="stat-value" style="font-size: 18px;">{{ $customer->plan->name }}</div>
                            <div class="stat-label">プラン</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Usage summary --}}
        @php
            $totalReqs = (int) ($summary['reqs'] ?? 0);
            $outBytes = (float) ($summary['output_bytes'] ?? 0);
            $inBytes = (float) ($summary['input_bytes'] ?? 0);
            $ratio = $inBytes > 0 ? ($outBytes / $inBytes) * 100 : null;
            $saved = $inBytes - $outBytes;
        @endphp

        @if ($totalReqs > 0)
            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="stat-tile text-center">
                        <div class="stat-value">{{ number_format($totalReqs) }}</div>
                        <div class="stat-label">リクエスト数</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-tile text-center">
                        <div class="stat-value">{{ number_format($outBytes / 1024 / 1024, 2) }}</div>
                        <div class="stat-label">配信 MB</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-tile text-center">
                        <div class="stat-value">{{ number_format($inBytes / 1024 / 1024, 2) }}</div>
                        <div class="stat-label">Origin MB</div>
                    </div>
                </div>
                @if ($ratio !== null)
                    <div class="col-6 col-lg-3">
                        <div class="stat-tile text-center">
                            <div class="stat-value text-success">{{ number_format($ratio, 1) }}%</div>
                            <div class="stat-label">圧縮率</div>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-1"></i> 直近 7 日のリクエストがありません (Analytics Engine の集計待ちかも)
            </div>
        @endif

        {{-- Daily chart --}}
        @if (! empty($byDay))
            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-graph-up me-2"></i> 日別推移 (7 日)</div>
                <div class="card-body">
                    <canvas id="dayChart" height="80"></canvas>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>日付</th><th class="text-end">req</th><th class="text-end">配信 MB</th><th class="text-end">origin MB</th></tr></thead>
                        <tbody>
                            @foreach ($byDay as $row)
                                <tr>
                                    <td><code>{{ $row['day'] }}</code></td>
                                    <td class="text-end">{{ number_format((int) $row['reqs']) }}</td>
                                    <td class="text-end">{{ number_format((float) $row['output_bytes'] / 1024 / 1024, 2) }}</td>
                                    <td class="text-end">{{ number_format((float) $row['input_bytes'] / 1024 / 1024, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Format breakdown --}}
        <div class="row g-3">
            @if (! empty($byFormat))
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><i class="bi bi-file-earmark-code me-2"></i> フォーマット別</div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-hover mb-0">
                                <thead><tr><th>format</th><th class="text-end">req</th><th class="text-end">origin</th><th class="text-end">配信</th><th class="text-end">圧縮</th></tr></thead>
                                <tbody>
                                    @foreach ($byFormat as $row)
                                        @php $rIn = (float)($row['input_bytes'] ?? 0); $rOut = (float)($row['output_bytes'] ?? 0); $rRatio = $rIn > 0 ? ($rOut/$rIn)*100 : null; @endphp
                                        <tr>
                                            <td><code>{{ $row['format'] ?: '—' }}</code></td>
                                            <td class="text-end">{{ number_format((int) $row['reqs']) }}</td>
                                            <td class="text-end">{{ number_format($rIn/1024/1024, 2) }}</td>
                                            <td class="text-end">{{ number_format($rOut/1024/1024, 2) }}</td>
                                            <td class="text-end text-success fw-bold">{{ $rRatio !== null ? number_format($rRatio,1).'%' : '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            @if (! empty($byCache))
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><i class="bi bi-hdd-stack me-2"></i> キャッシュ状態</div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-hover mb-0">
                                <thead><tr><th>cache_status</th><th class="text-end">req</th></tr></thead>
                                <tbody>
                                    @foreach ($byCache as $row)
                                        <tr>
                                            <td><code>{{ $row['cache_status'] ?: '—' }}</code></td>
                                            <td class="text-end">{{ number_format((int) $row['reqs']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-1"></i> 顧客情報が紐付いていません。マスターアカウント (admin) にお問い合わせください。
        </div>
    @endif
@endsection

@section('scripts')
    @if (! empty($byDay))
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            (function () {
                const rows = @json($byDay);
                const labels = rows.map(r => r.day);
                const reqs = rows.map(r => parseInt(r.reqs, 10));
                const outMb = rows.map(r => (parseFloat(r.output_bytes) / 1024 / 1024).toFixed(2));
                const inMb = rows.map(r => (parseFloat(r.input_bytes) / 1024 / 1024).toFixed(2));
                new Chart(document.getElementById('dayChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [
                            { label: 'origin MB', data: inMb, backgroundColor: 'rgba(251,146,60,.5)', yAxisID: 'y' },
                            { label: '配信 MB', data: outMb, backgroundColor: 'rgba(37,99,235,.7)', yAxisID: 'y' },
                            { label: 'req', data: reqs, type: 'line', borderColor: '#059669', backgroundColor: '#059669', yAxisID: 'y1', tension: .2, pointRadius: 3 },
                        ],
                    },
                    options: {
                        responsive: true,
                        interaction: { mode: 'index', intersect: false },
                        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
                        scales: {
                            y: { type: 'linear', position: 'left', title: { display: true, text: 'MB', font: { size: 11 } } },
                            y1: { type: 'linear', position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'req', font: { size: 11 } } },
                        },
                    },
                });
            })();
        </script>
    @endif
@endsection
