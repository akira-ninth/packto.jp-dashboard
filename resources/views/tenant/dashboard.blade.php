@extends('layouts.tenant')

@section('title', 'ダッシュボード | Packto')

@section('content')
    @if ($customer)
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4 mb-0 fw-bold">{{ $customer->display_name }}</h1>
            @if ($customer->active)
                <span class="badge badge-active"><i class="fa fa-check-circle me-1"></i>配信中</span>
            @else
                <span class="badge badge-inactive"><i class="fa fa-times-circle me-1"></i>停止中</span>
            @endif
        </div>

        {{-- Info tiles --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <a class="block block-rounded block-link-shadow" href="javascript:void(0)">
                    <div class="block-content block-content-full d-flex align-items-center">
                        <div class="me-3">
                            <i class="fa fa-globe fa-2x opacity-25 text-primary"></i>
                        </div>
                        <div>
                            <div class="fw-semibold" style="font-size: 1rem;">{{ $customer->subdomain }}.packto.jp</div>
                            <div class="fs-sm fw-semibold text-uppercase text-muted">配信ドメイン</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a class="block block-rounded block-link-shadow" href="javascript:void(0)">
                    <div class="block-content block-content-full d-flex align-items-center">
                        <div class="me-3">
                            <i class="fa fa-link fa-2x opacity-25 text-secondary"></i>
                        </div>
                        <div>
                            <div class="fw-semibold fs-sm">{{ $customer->origin_url }}</div>
                            <div class="fs-sm fw-semibold text-uppercase text-muted">Origin</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a class="block block-rounded block-link-shadow" href="javascript:void(0)">
                    <div class="block-content block-content-full d-flex align-items-center">
                        <div class="me-3">
                            <i class="fa fa-star fa-2x opacity-25" style="color: #6d28d9;"></i>
                        </div>
                        <div>
                            <div class="fw-semibold" style="font-size: 1.125rem;">{{ $customer->plan->name }}</div>
                            <div class="fs-sm fw-semibold text-uppercase text-muted">プラン</div>
                        </div>
                    </div>
                </a>
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
                    <a class="block block-rounded block-link-shadow text-center" href="javascript:void(0)">
                        <div class="block-content block-content-full">
                            <div class="fs-3 fw-semibold">{{ number_format($totalReqs) }}</div>
                            <div class="fs-sm fw-semibold text-uppercase text-muted">リクエスト数</div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-lg-3">
                    <a class="block block-rounded block-link-shadow text-center" href="javascript:void(0)">
                        <div class="block-content block-content-full">
                            <div class="fs-3 fw-semibold">{{ number_format($outBytes / 1024 / 1024, 2) }}</div>
                            <div class="fs-sm fw-semibold text-uppercase text-muted">配信 MB</div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-lg-3">
                    <a class="block block-rounded block-link-shadow text-center" href="javascript:void(0)">
                        <div class="block-content block-content-full">
                            <div class="fs-3 fw-semibold">{{ number_format($inBytes / 1024 / 1024, 2) }}</div>
                            <div class="fs-sm fw-semibold text-uppercase text-muted">Origin MB</div>
                        </div>
                    </a>
                </div>
                @if ($ratio !== null)
                    <div class="col-6 col-lg-3">
                        <a class="block block-rounded block-link-shadow text-center" href="javascript:void(0)">
                            <div class="block-content block-content-full">
                                <div class="fs-3 fw-semibold text-success">{{ number_format($ratio, 1) }}%</div>
                                <div class="fs-sm fw-semibold text-uppercase text-muted">圧縮率</div>
                            </div>
                        </a>
                    </div>
                @endif
            </div>
        @else
            <div class="alert alert-info">
                <i class="fa fa-info-circle me-1"></i> 直近 7 日のリクエストがありません (Analytics Engine の集計待ちかも)
            </div>
        @endif

        {{-- Daily chart --}}
        @if (! empty($byDay))
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title"><i class="fa fa-chart-line me-2 opacity-50"></i>日別推移 (7 日)</h3>
                </div>
                <div class="block-content">
                    <canvas id="dayChart" height="80"></canvas>
                </div>
                <div class="block-content p-0">
                    <table class="table table-sm table-hover table-vcenter mb-0">
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
                    <div class="block block-rounded">
                        <div class="block-header block-header-default">
                            <h3 class="block-title"><i class="fa fa-file-code me-2 opacity-50"></i>フォーマット別</h3>
                        </div>
                        <div class="block-content p-0">
                            <table class="table table-sm table-hover table-vcenter mb-0">
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
                    <div class="block block-rounded">
                        <div class="block-header block-header-default">
                            <h3 class="block-title"><i class="fa fa-hard-drive me-2 opacity-50"></i>キャッシュ状態</h3>
                        </div>
                        <div class="block-content p-0">
                            <table class="table table-sm table-hover table-vcenter mb-0">
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
            <i class="fa fa-triangle-exclamation me-1"></i> 顧客情報が紐付いていません。マスターアカウント (admin) にお問い合わせください。
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
