@extends('layouts.tenant')

@section('title', 'ダッシュボード | Packto')

@section('content')
    @if ($customer)
        <div class="peers ai-c jc-sb fxw-nw mT-10 mB-20">
            <div class="peer">
                <h4 class="c-grey-900">{{ $customer->display_name }}</h4>
            </div>
            <div class="peer">
                @if ($customer->active)
                    <span class="badge badge-active"><i class="fa fa-check-circle mR-5"></i>配信中</span>
                @else
                    <span class="badge badge-inactive"><i class="fa fa-times-circle mR-5"></i>停止中</span>
                @endif
            </div>
        </div>

        {{-- Info tiles --}}
        <div class="row gap-20 mB-20">
            <div class="col-md-4">
                <div class="layers bd bgc-white p-20">
                    <div class="layer w-100 mB-10"><h6 class="lh-1">配信ドメイン</h6></div>
                    <div class="layer w-100">
                        <div class="peers ai-c fxw-nw">
                            <div class="peer mR-15">
                                <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-blue-50 c-blue-500"><i class="ti-world"></i></span>
                            </div>
                            <div class="peer peer-greed"><span class="fw-600">{{ $customer->subdomain }}.packto.jp</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="layers bd bgc-white p-20">
                    <div class="layer w-100 mB-10"><h6 class="lh-1">対象URL</h6></div>
                    <div class="layer w-100">
                        <div class="peers ai-c fxw-nw">
                            <div class="peer mR-15">
                                <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-grey-100 c-grey-600"><i class="ti-link"></i></span>
                            </div>
                            <div class="peer peer-greed"><span class="fw-600 fsz-sm">{{ $customer->origin_url }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="layers bd bgc-white p-20">
                    <div class="layer w-100 mB-10"><h6 class="lh-1">プラン</h6></div>
                    <div class="layer w-100">
                        <div class="peers ai-c fxw-nw">
                            <div class="peer mR-15">
                                <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-purple-50 c-purple-500"><i class="ti-star"></i></span>
                            </div>
                            <div class="peer peer-greed"><span class="fw-600 fsz-lg">{{ $customer->plan->name }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Period selector --}}
        <div class="bgc-white bd bdrs-3 p-15 mB-20">
            <div class="peers ai-c jc-sb fxw-nw">
                <div class="peer">
                    <span class="c-grey-600 fsz-sm fw-600">表示期間</span>
                </div>
                <div class="peer">
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="?days=7" class="btn {{ $days === 7 ? 'btn-primary' : 'btn-secondary' }}">7日</a>
                        <a href="?days=30" class="btn {{ $days === 30 ? 'btn-primary' : 'btn-secondary' }}">1か月</a>
                        <a href="?days=90" class="btn {{ $days === 90 ? 'btn-primary' : 'btn-secondary' }}">3か月</a>
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
        @endphp

        @if ($totalReqs > 0)
            <div class="row gap-20 mB-20">
                <div class="col-md-3">
                    <div class="layers bd bgc-white p-20">
                        <div class="layer w-100 mB-10"><h6 class="lh-1">リクエスト数</h6></div>
                        <div class="layer w-100">
                            <div class="peers ai-sb fxw-nw">
                                <div class="peer peer-greed">
                                    <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-blue-50 c-blue-500"><i class="ti-bolt"></i></span>
                                </div>
                                <div class="peer"><span class="c-grey-900 fsz-lg fw-600">{{ number_format($totalReqs) }} <span class="fsz-sm c-grey-500 fw-400">件</span></span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="layers bd bgc-white p-20">
                        <div class="layer w-100 mB-10"><h6 class="lh-1">圧縮後転送量</h6></div>
                        <div class="layer w-100">
                            <div class="peers ai-sb fxw-nw">
                                <div class="peer peer-greed">
                                    <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-purple-50 c-purple-500"><i class="ti-cloud-up"></i></span>
                                </div>
                                <div class="peer"><span class="c-grey-900 fsz-lg fw-600">{{ number_format($outBytes / 1024 / 1024, 2) }} <span class="fsz-sm c-grey-500 fw-400">MB</span></span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="layers bd bgc-white p-20">
                        <div class="layer w-100 mB-10"><h6 class="lh-1">無圧縮時のサイズ</h6></div>
                        <div class="layer w-100">
                            <div class="peers ai-sb fxw-nw">
                                <div class="peer peer-greed">
                                    <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-orange-50 c-orange-500"><i class="ti-cloud-down"></i></span>
                                </div>
                                <div class="peer"><span class="c-grey-900 fsz-lg fw-600">{{ number_format($inBytes / 1024 / 1024, 2) }} <span class="fsz-sm c-grey-500 fw-400">MB</span></span></div>
                            </div>
                        </div>
                    </div>
                </div>
                @if ($ratio !== null)
                    <div class="col-md-3">
                        <div class="layers bd bgc-white p-20">
                            <div class="layer w-100 mB-10"><h6 class="lh-1">圧縮率</h6></div>
                            <div class="layer w-100">
                                <div class="peers ai-sb fxw-nw">
                                    <div class="peer peer-greed">
                                        <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-green-50 c-green-500"><i class="ti-arrow-down"></i></span>
                                    </div>
                                    <div class="peer"><span class="c-green-500 fsz-lg fw-600">{{ number_format($ratio, 1) }}%</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="alert alert-info mB-20">
                <i class="fa fa-info-circle mR-5"></i> 直近 {{ $days }} 日のリクエストがありません
            </div>
        @endif

        {{-- Charts --}}
        @if (! empty($byDay))
            {{-- Line chart: requests --}}
            <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <h4 class="c-grey-900 mB-20"><i class="ti-stats-up mR-10 c-grey-500"></i>リクエスト推移 ({{ $days }}日)</h4>
                <canvas id="reqLineChart" height="70"></canvas>
            </div>

            {{-- Line chart: bytes --}}
            <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <h4 class="c-grey-900 mB-20"><i class="ti-bar-chart mR-10 c-grey-500"></i>配信量推移 ({{ $days }}日)</h4>
                <canvas id="bytesLineChart" height="70"></canvas>
            </div>

            {{-- Daily table --}}
            <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <h4 class="c-grey-900 mB-20"><i class="ti-layout-list-post mR-10 c-grey-500"></i>日別詳細</h4>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>日付</th><th class="text-end">リクエスト</th><th class="text-end">圧縮後</th><th class="text-end">無圧縮</th><th class="text-end">圧縮率</th></tr></thead>
                        <tbody>
                            @foreach ($byDay as $row)
                                @php
                                    $dIn = (float)($row['input_bytes'] ?? 0);
                                    $dOut = (float)($row['output_bytes'] ?? 0);
                                    $dRatio = $dIn > 0 ? ($dOut / $dIn) * 100 : null;
                                @endphp
                                <tr>
                                    <td><code>{{ $row['day'] }}</code></td>
                                    <td class="text-end">{{ number_format((int) $row['reqs']) }} 件</td>
                                    <td class="text-end">{{ number_format($dOut / 1024 / 1024, 2) }} MB</td>
                                    <td class="text-end">{{ number_format($dIn / 1024 / 1024, 2) }} MB</td>
                                    <td class="text-end c-green-500 fw-600">{{ $dRatio !== null ? number_format($dRatio, 1).'%' : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Format + Cache breakdown --}}
        <div class="row gap-20">
            @if (! empty($byFormat))
                <div class="col-lg-6">
                    <div class="bgc-white bd bdrs-3 p-20 mB-20">
                        <h4 class="c-grey-900 mB-20"><i class="ti-file mR-10 c-grey-500"></i>フォーマット別</h4>
                        <table class="table table-sm">
                            <thead><tr><th>形式</th><th class="text-end">リクエスト</th><th class="text-end">無圧縮</th><th class="text-end">圧縮後</th><th class="text-end">圧縮率</th></tr></thead>
                            <tbody>
                                @foreach ($byFormat as $row)
                                    @php $rIn = (float)($row['input_bytes'] ?? 0); $rOut = (float)($row['output_bytes'] ?? 0); $rRatio = $rIn > 0 ? ($rOut/$rIn)*100 : null; @endphp
                                    <tr>
                                        <td><code>{{ $row['format'] ?: '—' }}</code></td>
                                        <td class="text-end">{{ number_format((int) $row['reqs']) }}</td>
                                        <td class="text-end">{{ number_format($rIn/1024/1024, 2) }}</td>
                                        <td class="text-end">{{ number_format($rOut/1024/1024, 2) }}</td>
                                        <td class="text-end c-green-500 fw-600">{{ $rRatio !== null ? number_format($rRatio,1).'%' : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if (! empty($byCache))
                <div class="col-lg-6">
                    <div class="bgc-white bd bdrs-3 p-20 mB-20">
                        <h4 class="c-grey-900 mB-20"><i class="ti-server mR-10 c-grey-500"></i>キャッシュ状態</h4>
                        <table class="table table-sm">
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
            @endif
        </div>
    @else
        <div class="alert alert-warning mT-20">
            <i class="fa fa-triangle-exclamation mR-5"></i> 顧客情報が紐付いていません。マスターアカウント (admin) にお問い合わせください。
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
                const outMb = rows.map(r => parseFloat((parseFloat(r.output_bytes) / 1024 / 1024).toFixed(2)));
                const inMb = rows.map(r => parseFloat((parseFloat(r.input_bytes) / 1024 / 1024).toFixed(2)));

                const commonOpts = {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
                };

                // Chart 1: Request count line chart
                new Chart(document.getElementById('reqLineChart').getContext('2d'), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: 'リクエスト数',
                            data: reqs,
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 3,
                            pointBackgroundColor: '#6366f1',
                        }],
                    },
                    options: {
                        ...commonOpts,
                        scales: { y: { beginAtZero: true, title: { display: true, text: 'req', font: { size: 11 } } } },
                    },
                });

                // Chart 2: Bytes line chart (origin vs output)
                new Chart(document.getElementById('bytesLineChart').getContext('2d'), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: '無圧縮 MB',
                                data: inMb,
                                borderColor: '#f97316',
                                backgroundColor: 'rgba(249, 115, 22, 0.1)',
                                fill: true,
                                tension: 0.3,
                                pointRadius: 3,
                                pointBackgroundColor: '#f97316',
                            },
                            {
                                label: '圧縮後 MB',
                                data: outMb,
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                fill: true,
                                tension: 0.3,
                                pointRadius: 3,
                                pointBackgroundColor: '#3b82f6',
                            },
                        ],
                    },
                    options: {
                        ...commonOpts,
                        scales: { y: { beginAtZero: true, title: { display: true, text: 'MB', font: { size: 11 } } } },
                    },
                });
            })();
        </script>
    @endif
@endsection
