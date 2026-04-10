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
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left mR-5"></i> 一覧に戻る
            </a>
        </div>
    </div>

    @if (session('temp_credentials'))
        @php $mailSent = session('temp_credentials.mail_sent'); @endphp
        <div class="bgc-white bd bdrs-3 p-20 mB-20" style="border-left: 4px solid #f59e0b;">
            <h5 class="c-grey-900 mB-15"><i class="fa fa-triangle-exclamation c-orange-500 mR-5"></i> 初期ログイン情報 (この画面でのみ表示されます)</h5>
            @if ($mailSent === true)
                <div class="alert alert-success fsz-sm pY-10 mB-15">
                    <i class="fa fa-check-circle mR-5"></i> 招待メールを <code>{{ session('temp_credentials.email') }}</code> に送信しました。
                </div>
            @elseif ($mailSent === false)
                <div class="alert alert-danger fsz-sm pY-10 mB-15">
                    <i class="fa fa-triangle-exclamation mR-5"></i> メール送信に失敗しました。下記の情報を手動で控えて顧客に伝えてください。
                </div>
            @endif
            <p class="c-grey-600 fsz-sm mB-15">
                次にこのページをリロードすると消えます。
                顧客は初回ログイン後に <code>/account</code> でパスワードを変更してください。
            </p>
            <table class="table table-sm table-borderless" style="max-width: 500px;">
                <tr><th style="width: 140px;">ログイン URL</th><td><code>https://app.packto.jp/login</code></td></tr>
                <tr><th>メール</th><td><code>{{ session('temp_credentials.email') }}</code></td></tr>
                <tr><th>パスワード</th><td><code class="bgc-white pX-10 pY-5 bdrs-3" style="font-size: .875rem;">{{ session('temp_credentials.password') }}</code></td></tr>
            </table>
        </div>
    @endif

    {{-- Customer details --}}
    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <h4 class="c-grey-900 mB-20"><i class="ti-home mR-10 c-grey-500"></i>顧客情報</h4>
        <table class="table">
            <tr><th style="width: 180px;">サブドメイン</th><td><code>{{ $customer->subdomain }}.packto.jp</code></td></tr>
            <tr><th>Origin URL</th><td><a href="{{ $customer->origin_url }}" target="_blank" rel="noopener">{{ $customer->origin_url }} <i class="fa fa-up-right-from-square" style="font-size: .6875rem;"></i></a></td></tr>
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

    {{-- Usage summary --}}
    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <h4 class="c-grey-900 mB-20"><i class="ti-bar-chart mR-10 c-grey-500"></i>使用量サマリ (直近 7 日)</h4>
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
                        <div class="layer w-100 mB-10">
                            <h6 class="lh-1">req</h6>
                        </div>
                        <div class="layer w-100">
                            <div class="peers ai-sb fxw-nw">
                                <div class="peer peer-greed">
                                    <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-blue-50 c-blue-500">
                                        <i class="ti-bolt"></i>
                                    </span>
                                </div>
                                <div class="peer">
                                    <span class="c-grey-900 fsz-lg fw-600">{{ number_format($sReqs) }}</span>
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
                                    <span class="c-grey-900 fsz-lg fw-600">{{ number_format($sOut / 1024 / 1024, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="layers bd bgc-white p-20">
                        <div class="layer w-100 mB-10">
                            <h6 class="lh-1">origin MB</h6>
                        </div>
                        <div class="layer w-100">
                            <div class="peers ai-sb fxw-nw">
                                <div class="peer peer-greed">
                                    <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-orange-50 c-orange-500">
                                        <i class="ti-cloud-down"></i>
                                    </span>
                                </div>
                                <div class="peer">
                                    <span class="c-grey-900 fsz-lg fw-600">{{ number_format($sIn / 1024 / 1024, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if ($sRatio !== null)
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
                                        <span class="c-green-500 fsz-lg fw-600">{{ number_format($sRatio, 1) }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            @if (! empty($usageByFormat))
                <table class="table">
                    <thead>
                        <tr>
                            <th>format</th>
                            <th class="text-end">req</th>
                            <th class="text-end">origin MB</th>
                            <th class="text-end">配信 MB</th>
                            <th class="text-end">圧縮率</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usageByFormat as $row)
                            @php
                                $rIn = (float) ($row['input_bytes'] ?? 0);
                                $rOut = (float) ($row['output_bytes'] ?? 0);
                                $rRatio = $rIn > 0 ? ($rOut / $rIn) * 100 : null;
                            @endphp
                            <tr>
                                <td><code>{{ $row['format'] ?: '(none)' }}</code></td>
                                <td class="text-end">{{ number_format((int) $row['reqs']) }}</td>
                                <td class="text-end">{{ number_format($rIn / 1024 / 1024, 2) }}</td>
                                <td class="text-end">{{ number_format($rOut / 1024 / 1024, 2) }}</td>
                                <td class="text-end c-green-500 fw-600">
                                    {{ $rRatio !== null ? number_format($rRatio, 1).'%' : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @else
            <p class="c-grey-600 mB-0">直近 7 日のリクエストがありません</p>
        @endif
    </div>

    {{-- Users --}}
    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <h4 class="c-grey-900 mB-20"><i class="ti-user mR-10 c-grey-500"></i>所属ユーザ ({{ $customer->users->count() }})</h4>
        @if ($customer->users->isEmpty())
            <p class="c-grey-600 mB-0">ユーザが登録されていません</p>
        @else
            <table class="table mB-20">
                <thead>
                    <tr><th>名前</th><th>メール</th><th class="text-end"></th></tr>
                </thead>
                <tbody>
                    @foreach ($customer->users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.customers.users.destroy', [$customer, $user]) }}" class="d-ib" onsubmit="return confirm('本当に {{ $user->email }} を削除しますか?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fa fa-trash mR-5"></i> 削除
                                    </button>
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
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa fa-plus"></i> 追加
                    </button>
                </div>
            </div>
            <p class="c-grey-600 mT-10 mB-0" style="font-size: .75rem;">パスワードは自動生成され、追加完了画面で 1 度だけ表示されます。</p>
        </form>
    </div>
@endsection
