@extends('layouts.tenant')

@section('title', 'ダッシュボード | Packto')

@section('content')
    @if ($customer)
        <div class="peers ai-c jc-sb fxw-nw mT-10 mB-30">
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
                    <div class="layer w-100 mB-10">
                        <h6 class="lh-1">配信ドメイン</h6>
                    </div>
                    <div class="layer w-100">
                        <div class="peers ai-c fxw-nw">
                            <div class="peer mR-15">
                                <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-blue-50 c-blue-500">
                                    <i class="ti-world"></i>
                                </span>
                            </div>
                            <div class="peer peer-greed">
                                <span class="fw-600">{{ $customer->subdomain }}.packto.jp</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="layers bd bgc-white p-20">
                    <div class="layer w-100 mB-10">
                        <h6 class="lh-1">Origin</h6>
                    </div>
                    <div class="layer w-100">
                        <div class="peers ai-c fxw-nw">
                            <div class="peer mR-15">
                                <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-grey-100 c-grey-600">
                                    <i class="ti-link"></i>
                                </span>
                            </div>
                            <div class="peer peer-greed">
                                <span class="fw-600 fsz-sm">{{ $customer->origin_url }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="layers bd bgc-white p-20">
                    <div class="layer w-100 mB-10">
                        <h6 class="lh-1">プラン</h6>
                    </div>
                    <div class="layer w-100">
                        <div class="peers ai-c fxw-nw">
                            <div class="peer mR-15">
                                <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-purple-50 c-purple-500">
                                    <i class="ti-star"></i>
                                </span>
                            </div>
                            <div class="peer peer-greed">
                                <span class="fw-600 fsz-lg">{{ $customer->plan->name }}</span>
                            </div>
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
            <div class="row gap-20 mB-20">
                <div class="col-md-3">
                    <div class="layers bd bgc-white p-20">
                        <div class="layer w-100 mB-10">
                            <h6 class="lh-1">リクエスト数</h6>
                        </div>
                        <div class="layer w-100">
                            <div class="peers ai-sb fxw-nw">
                                <div class="peer peer-greed">
                                    <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-blue-50 c-blue-500">
                                        <i class="ti-bolt"></i>
                                    </span>
                                </div>
                                <div class="peer">
                                    <span class="c-grey-900 fsz-lg fw-600">{{ number_format($totalReqs) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="layers bd bgc-white p-20">
                        <div class="layer w-100 mB-10">
                            <h6 class="lh-1">配信 MB</h6>
                        </div>
                        <div class="layer w-100">
                            <div class="peers ai-sb fxw-nw">
                                <div class="peer peer-greed">
                                    <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-purple-50 c-purple-500">
                                        <i class="ti-cloud-up"></i>
                                    </span>
                                </div>
                                <div class="peer">
                                    <span class="c-grey-900 fsz-lg fw-600">{{ number_format($outBytes / 1024 / 1024, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="layers bd bgc-white p-20">
                        <div class="layer w-100 mB-10">
                            <h6 class="lh-1">Origin MB</h6>
                        </div>
                        <div class="layer w-100">
                            <div class="peers ai-sb fxw-nw">
                                <div class="peer peer-greed">
                                    <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-orange-50 c-orange-500">
                                        <i class="ti-cloud-down"></i>
                                    </span>
                                </div>
                                <div class="peer">
                                    <span class="c-grey-900 fsz-lg fw-600">{{ number_format($inBytes / 1024 / 1024, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if ($ratio !== null)
                    <div class="col-md-3">
                        <div class="layers bd bgc-white p-20">
                            <div class="layer w-100 mB-10">
                                <h6 class="lh-1">圧縮率</h6>
                            </div>
                            <div class="layer w-100">
                                <div class="peers ai-sb fxw-nw">
                                    <div class="peer peer-greed">
                                        <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-green-50 c-green-500">
                                            <i class="ti-arrow-down"></i>
                                        </span>
                                    </div>
                                    <div class="peer">
                                        <span class="c-green-500 fsz-lg fw-600">{{ number_format($ratio, 1) }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="alert alert-info mB-20">
                <i class="fa fa-info-circle mR-5"></i> 直近 7 日のリクエストがありません (Analytics Engine の集計待ちかも)
            </div>
        @endif

        {{-- Daily chart --}}
        @if (! empty($byDay))
            <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <h4 class="c-grey-900 mB-20"><i class="ti-bar-chart mR-10 c-grey-500"></i>日別推移 (7 日)</h4>
                <canvas id="dayChart" height="80"></canvas>
                <table class="table table-sm mT-20">
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
        @endif

        {{-- Format breakdown --}}
        <div class="row gap-20">
            @if (! empty($byFormat))
                <div class="col-lg-6">
                    <div class="bgc-white bd bdrs-3 p-20 mB-20">
                        <h4 class="c-grey-900 mB-20"><i class="ti-file mR-10 c-grey-500"></i>フォーマット別</h4>
                        <table class="table table-sm">
                            <thead><tr><th>format</th><th class="text-end">req</th><th class="text-end">origin</th><th class="text-end">配信</th><th class="text-end">圧縮</th></tr></thead>
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
