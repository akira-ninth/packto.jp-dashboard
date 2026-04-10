@extends('layouts.admin')

@section('title', $customer->display_name . ' | Packto Console')

@section('content')
    <div class="peers ai-c jc-sb fxw-nw mT-10 mB-30">
        <div class="peer">
            <h4 class="c-grey-900">{{ $customer->display_name }}</h4>
        </div>
        <div class="peer">
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-primary btn-sm mR-10">
                <i class="fa fa-pencil mR-5"></i> 編集
            </a>
            <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" class="d-ib mR-10" onsubmit="return confirm('{{ $customer->display_name }} を削除しますか？\n所属ユーザも全て削除され、Cloudflare KV からも消去されます。この操作は取り消せません。');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fa fa-trash mR-5"></i> 削除
                </button>
            </form>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left mR-5"></i> 一覧
            </a>
        </div>
    </div>

    {{-- Temp credentials --}}
    @if (session('temp_credentials'))
        @php $mailSent = session('temp_credentials.mail_sent'); @endphp
        <div class="bgc-white bd bdrs-3 p-20 mB-20" style="border-left: 4px solid #f59e0b;">
            <h5 class="c-grey-900 mB-15"><i class="fa fa-triangle-exclamation c-orange-500 mR-5"></i> 初期ログイン情報</h5>
            @if ($mailSent === true)
                <div class="alert alert-success fsz-sm pY-10 mB-15"><i class="fa fa-check-circle mR-5"></i> 招待メールを <code>{{ session('temp_credentials.email') }}</code> に送信しました。</div>
            @elseif ($mailSent === false)
                <div class="alert alert-danger fsz-sm pY-10 mB-15"><i class="fa fa-triangle-exclamation mR-5"></i> メール送信に失敗しました。</div>
            @endif
            <table class="table table-sm table-borderless" style="max-width: 500px;">
                <tr><th style="width: 140px;">ログイン URL</th><td><code>https://app.packto.jp/login</code></td></tr>
                <tr><th>メール</th><td><code>{{ session('temp_credentials.email') }}</code></td></tr>
                <tr><th>パスワード</th><td><code class="bgc-white pX-10 pY-5 bdrs-3">{{ session('temp_credentials.password') }}</code></td></tr>
            </table>
        </div>
    @endif

    {{-- Customer info --}}
    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <h4 class="c-grey-900 mB-20"><i class="ti-home mR-10 c-grey-500"></i>顧客情報</h4>
        <table class="table">
            <tr><th style="width: 180px;">サブドメイン</th><td><code>{{ $customer->subdomain }}.packto.jp</code></td></tr>
            <tr><th>Origin URL</th><td><a href="{{ $customer->origin_url }}" target="_blank" rel="noopener">{{ $customer->origin_url }}</a></td></tr>
            <tr><th>プラン</th><td><span class="badge badge-plan-{{ $customer->plan->slug }}">{{ $customer->plan->name }}</span></td></tr>
            <tr><th>状態</th><td>
                @if ($customer->active)
                    <span class="badge badge-active">有効</span>
                @else
                    <span class="badge badge-inactive">停止</span>
                @endif
            </td></tr>
            <tr><th>作成日</th><td>{{ $customer->created_at?->format('Y-m-d H:i') }}</td></tr>
        </table>
    </div>

    {{-- Period selector --}}
    <div class="bgc-white bd bdrs-3 p-15 mB-20">
        <div class="peers ai-c jc-sb fxw-nw">
            <div class="peer"><span class="c-grey-600 fsz-sm fw-600">配信データ — 表示期間</span></div>
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
        $sReqs = (int) ($usageSummary['reqs'] ?? 0);
        $sOut = (float) ($usageSummary['output_bytes'] ?? 0);
        $sIn = (float) ($usageSummary['input_bytes'] ?? 0);
        $sRatio = $sIn > 0 ? ($sOut / $sIn) * 100 : null;
    @endphp

    @if ($sReqs > 0)
        <div class="row gap-20 mB-20">
            <div class="col-md-3">
                <div class="layers bd bgc-white p-20">
                    <div class="layer w-100 mB-10"><h6 class="lh-1">リクエスト数</h6></div>
                    <div class="layer w-100">
                        <div class="peers ai-sb fxw-nw">
                            <div class="peer peer-greed"><span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-blue-50 c-blue-500"><i class="ti-bolt"></i></span></div>
                            <div class="peer"><span class="c-grey-900 fsz-lg fw-600">{{ number_format($sReqs) }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="layers bd bgc-white p-20">
                    <div class="layer w-100 mB-10"><h6 class="lh-1">配信 MB</h6></div>
                    <div class="layer w-100">
                        <div class="peers ai-sb fxw-nw">
                            <div class="peer peer-greed"><span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-purple-50 c-purple-500"><i class="ti-cloud-up"></i></span></div>
                            <div class="peer"><span class="c-grey-900 fsz-lg fw-600">{{ number_format($sOut / 1024 / 1024, 2) }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="layers bd bgc-white p-20">
                    <div class="layer w-100 mB-10"><h6 class="lh-1">Origin MB</h6></div>
                    <div class="layer w-100">
                        <div class="peers ai-sb fxw-nw">
                            <div class="peer peer-greed"><span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-orange-50 c-orange-500"><i class="ti-cloud-down"></i></span></div>
                            <div class="peer"><span class="c-grey-900 fsz-lg fw-600">{{ number_format($sIn / 1024 / 1024, 2) }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
            @if ($sRatio !== null)
                <div class="col-md-3">
                    <div class="layers bd bgc-white p-20">
                        <div class="layer w-100 mB-10"><h6 class="lh-1">圧縮率</h6></div>
                        <div class="layer w-100">
                            <div class="peers ai-sb fxw-nw">
                                <div class="peer peer-greed"><span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-green-50 c-green-500"><i class="ti-arrow-down"></i></span></div>
                                <div class="peer"><span class="c-green-500 fsz-lg fw-600">{{ number_format($sRatio, 1) }}%</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Line charts --}}
        @if (! empty($usageByDay))
            <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <h4 class="c-grey-900 mB-20"><i class="ti-stats-up mR-10 c-grey-500"></i>リクエスト推移</h4>
                <canvas id="reqLineChart" height="70"></canvas>
            </div>
            <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <h4 class="c-grey-900 mB-20"><i class="ti-bar-chart mR-10 c-grey-500"></i>配信量推移</h4>
                <canvas id="bytesLineChart" height="70"></canvas>
            </div>

            {{-- Daily table --}}
            <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <h4 class="c-grey-900 mB-20"><i class="ti-layout-list-post mR-10 c-grey-500"></i>日別詳細</h4>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>日付</th><th class="text-end">req</th><th class="text-end">配信 MB</th><th class="text-end">origin MB</th><th class="text-end">圧縮率</th></tr></thead>
                        <tbody>
                            @foreach ($usageByDay as $row)
                                @php $dIn=(float)($row['input_bytes']??0); $dOut=(float)($row['output_bytes']??0); $dR=$dIn>0?($dOut/$dIn)*100:null; @endphp
                                <tr>
                                    <td><code>{{ $row['day'] }}</code></td>
                                    <td class="text-end">{{ number_format((int)$row['reqs']) }}</td>
                                    <td class="text-end">{{ number_format($dOut/1024/1024, 2) }}</td>
                                    <td class="text-end">{{ number_format($dIn/1024/1024, 2) }}</td>
                                    <td class="text-end c-green-500 fw-600">{{ $dR !== null ? number_format($dR,1).'%' : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Format + Cache --}}
        <div class="row gap-20">
            @if (! empty($usageByFormat))
                <div class="col-lg-6">
                    <div class="bgc-white bd bdrs-3 p-20 mB-20">
                        <h4 class="c-grey-900 mB-20"><i class="ti-file mR-10 c-grey-500"></i>フォーマット別</h4>
                        <table class="table table-sm">
                            <thead><tr><th>format</th><th class="text-end">req</th><th class="text-end">origin</th><th class="text-end">配信</th><th class="text-end">圧縮</th></tr></thead>
                            <tbody>
                                @foreach ($usageByFormat as $row)
                                    @php $rIn=(float)($row['input_bytes']??0); $rOut=(float)($row['output_bytes']??0); $rR=$rIn>0?($rOut/$rIn)*100:null; @endphp
                                    <tr>
                                        <td><code>{{ $row['format'] ?: '—' }}</code></td>
                                        <td class="text-end">{{ number_format((int)$row['reqs']) }}</td>
                                        <td class="text-end">{{ number_format($rIn/1024/1024, 2) }}</td>
                                        <td class="text-end">{{ number_format($rOut/1024/1024, 2) }}</td>
                                        <td class="text-end c-green-500 fw-600">{{ $rR !== null ? number_format($rR,1).'%' : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
            @if (! empty($usageByCache))
                <div class="col-lg-6">
                    <div class="bgc-white bd bdrs-3 p-20 mB-20">
                        <h4 class="c-grey-900 mB-20"><i class="ti-server mR-10 c-grey-500"></i>キャッシュ状態</h4>
                        <table class="table table-sm">
                            <thead><tr><th>cache_status</th><th class="text-end">req</th></tr></thead>
                            <tbody>
                                @foreach ($usageByCache as $row)
                                    <tr>
                                        <td><code>{{ $row['cache_status'] ?: '—' }}</code></td>
                                        <td class="text-end">{{ number_format((int)$row['reqs']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="alert alert-info mB-20"><i class="fa fa-info-circle mR-5"></i> 直近 {{ $days }} 日のリクエストがありません</div>
    @endif

    {{-- Large images --}}
    @if (! empty($largeImages))
        <div class="bgc-white bd bdrs-3 p-20 mB-20">
            <h4 class="c-grey-900 mB-20"><i class="ti-image mR-10 c-grey-500"></i>直近の大きい配信画像 (7 日 / 50KB 以上)</h4>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>パス</th><th>format</th><th class="text-end">origin</th><th class="text-end">配信</th><th class="text-end">圧縮率</th><th>日時</th></tr></thead>
                    <tbody>
                        @foreach ($largeImages as $img)
                            @php
                                $iIn = (float)($img['input_bytes'] ?? 0);
                                $iOut = (float)($img['output_bytes'] ?? 0);
                                $iR = $iIn > 0 ? ($iOut / $iIn) * 100 : null;
                            @endphp
                            <tr>
                                <td><code class="fsz-sm" style="word-break: break-all;">{{ $img['path'] ?? '—' }}</code></td>
                                <td><code>{{ $img['format'] ?? '—' }}</code></td>
                                <td class="text-end">{{ $iIn > 0 ? number_format($iIn / 1024, 1).' KB' : '—' }}</td>
                                <td class="text-end">{{ $iOut > 0 ? number_format($iOut / 1024, 1).' KB' : '—' }}</td>
                                <td class="text-end c-green-500 fw-600">{{ $iR !== null ? number_format($iR, 1).'%' : '—' }}</td>
                                <td><span class="fsz-sm c-grey-500">{{ $img['timestamp'] ?? '' }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Users --}}
    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <h4 class="c-grey-900 mB-20"><i class="ti-user mR-10 c-grey-500"></i>所属ユーザ ({{ $customer->users->count() }})</h4>
        @if ($customer->users->isEmpty())
            <p class="c-grey-600 mB-0">ユーザが登録されていません</p>
        @else
            <table class="table mB-20">
                <thead><tr><th>名前</th><th>メール</th><th class="text-end"></th></tr></thead>
                <tbody>
                    @foreach ($customer->users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.customers.users.destroy', [$customer, $user]) }}" class="d-ib" onsubmit="return confirm('本当に {{ $user->email }} を削除しますか?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash mR-5"></i>削除</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        <hr>
        <h6 class="fw-600 mT-20 mB-15"><i class="ti-plus mR-5 c-grey-500"></i> ユーザを追加</h6>
        <form method="POST" action="{{ route('admin.customers.users.store', $customer) }}">
            @csrf
            <div class="row gap-20">
                <div class="col-md-5">
                    <label class="form-label">名前</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                    @error('name')<div class="c-red-500 mT-5 fsz-sm">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label">メールアドレス</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                    @error('email')<div class="c-red-500 mT-5 fsz-sm">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa fa-plus"></i> 追加</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    @if (! empty($usageByDay))
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            (function () {
                const rows = @json($usageByDay);
                const labels = rows.map(r => r.day);
                const reqs = rows.map(r => parseInt(r.reqs, 10));
                const outMb = rows.map(r => parseFloat((parseFloat(r.output_bytes) / 1024 / 1024).toFixed(2)));
                const inMb = rows.map(r => parseFloat((parseFloat(r.input_bytes) / 1024 / 1024).toFixed(2)));
                const commonOpts = {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
                };
                new Chart(document.getElementById('reqLineChart').getContext('2d'), {
                    type: 'line',
                    data: { labels, datasets: [{ label: 'リクエスト数', data: reqs, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,.1)', fill: true, tension: .3, pointRadius: 3, pointBackgroundColor: '#6366f1' }] },
                    options: { ...commonOpts, scales: { y: { beginAtZero: true, title: { display: true, text: 'req', font: { size: 11 } } } } },
                });
                new Chart(document.getElementById('bytesLineChart').getContext('2d'), {
                    type: 'line',
                    data: { labels, datasets: [
                        { label: 'origin MB', data: inMb, borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,.1)', fill: true, tension: .3, pointRadius: 3, pointBackgroundColor: '#f97316' },
                        { label: '配信 MB', data: outMb, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,.1)', fill: true, tension: .3, pointRadius: 3, pointBackgroundColor: '#3b82f6' },
                    ] },
                    options: { ...commonOpts, scales: { y: { beginAtZero: true, title: { display: true, text: 'MB', font: { size: 11 } } } } },
                });
            })();
        </script>
    @endif
@endsection
