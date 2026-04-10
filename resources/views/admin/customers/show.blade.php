@extends('layouts.admin')

@section('title', $customer->display_name . ' | Packto Console')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0">{{ $customer->display_name }}</h1>
        <div>
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil-square"></i> 編集
            </a>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> 一覧に戻る
            </a>
        </div>
    </div>

    @if (session('temp_credentials'))
        @php $mailSent = session('temp_credentials.mail_sent'); @endphp
        <div class="alert alert-warning">
            <h5 class="alert-heading mb-2"><i class="bi bi-exclamation-triangle"></i> 初期ログイン情報 (この画面でのみ表示されます)</h5>
            @if ($mailSent === true)
                <div class="alert alert-success py-2 mb-2" style="font-size: 13px;">
                    <i class="bi bi-check-circle"></i> 招待メールを <code>{{ session('temp_credentials.email') }}</code> に送信しました。
                </div>
            @elseif ($mailSent === false)
                <div class="alert alert-danger py-2 mb-2" style="font-size: 13px;">
                    <i class="bi bi-exclamation-triangle"></i> メール送信に失敗しました。下記の情報を手動で控えて顧客に伝えてください。
                </div>
            @endif
            <p class="mb-2" style="font-size: 13px;">
                次にこのページをリロードすると消えます。
                顧客は初回ログイン後に <code>/account</code> でパスワードを変更してください。
            </p>
            <table class="table table-sm table-borderless mb-0" style="max-width: 500px;">
                <tr><th style="width: 140px;">ログイン URL</th><td><code>https://app.packto.jp/login</code></td></tr>
                <tr><th>メール</th><td><code>{{ session('temp_credentials.email') }}</code></td></tr>
                <tr><th>パスワード</th><td><code class="bg-white px-2 py-1 rounded" style="font-size: 14px;">{{ session('temp_credentials.password') }}</code></td></tr>
            </table>
        </div>
    @endif

    {{-- Customer details --}}
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center">
            <i class="bi bi-building me-2"></i> 顧客情報
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <tr><th style="width: 180px;">サブドメイン</th><td><code>{{ $customer->subdomain }}.packto.jp</code></td></tr>
                <tr><th>Origin URL</th><td><a href="{{ $customer->origin_url }}" target="_blank" rel="noopener">{{ $customer->origin_url }} <i class="bi bi-box-arrow-up-right" style="font-size: 11px;"></i></a></td></tr>
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
    </div>

    {{-- Usage summary --}}
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center">
            <i class="bi bi-bar-chart me-2"></i> 使用量サマリ (直近 7 日)
        </div>
        <div class="card-body">
            @php
                $sReqs = (int) ($usageSummary['reqs'] ?? 0);
                $sOut = (float) ($usageSummary['output_bytes'] ?? 0);
                $sIn = (float) ($usageSummary['input_bytes'] ?? 0);
                $sRatio = $sIn > 0 ? ($sOut / $sIn) * 100 : null;
            @endphp
            @if ($sReqs > 0)
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="stat-tile">
                            <div class="d-flex align-items-center mb-2">
                                <div class="stat-icon" style="background: #3b82f6;"><i class="bi bi-lightning"></i></div>
                            </div>
                            <div class="stat-value">{{ number_format($sReqs) }}</div>
                            <div class="stat-label">req</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-tile">
                            <div class="d-flex align-items-center mb-2">
                                <div class="stat-icon" style="background: #8b5cf6;"><i class="bi bi-cloud-arrow-up"></i></div>
                            </div>
                            <div class="stat-value">{{ number_format($sOut / 1024 / 1024, 2) }}</div>
                            <div class="stat-label">配信 MB</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-tile">
                            <div class="d-flex align-items-center mb-2">
                                <div class="stat-icon" style="background: #f59e0b;"><i class="bi bi-cloud-arrow-down"></i></div>
                            </div>
                            <div class="stat-value">{{ number_format($sIn / 1024 / 1024, 2) }}</div>
                            <div class="stat-label">origin MB</div>
                        </div>
                    </div>
                    @if ($sRatio !== null)
                        <div class="col-6 col-md-3">
                            <div class="stat-tile">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="stat-icon" style="background: #059669;"><i class="bi bi-arrows-collapse"></i></div>
                                </div>
                                <div class="stat-value" style="color: #059669;">{{ number_format($sRatio, 1) }}%</div>
                                <div class="stat-label">圧縮率</div>
                            </div>
                        </div>
                    @endif
                </div>

                @if (! empty($usageByFormat))
                    <table class="table table-hover">
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
                                    <td class="text-end" style="color: #059669; font-weight: 600;">
                                        {{ $rRatio !== null ? number_format($rRatio, 1).'%' : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @else
                <p class="text-muted mb-0">直近 7 日のリクエストがありません</p>
            @endif
        </div>
    </div>

    {{-- Users --}}
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center">
            <i class="bi bi-people me-2"></i> 所属ユーザ ({{ $customer->users->count() }})
        </div>
        <div class="card-body">
            @if ($customer->users->isEmpty())
                <p class="text-muted mb-0">ユーザが登録されていません</p>
            @else
                <table class="table table-hover">
                    <thead>
                        <tr><th>名前</th><th>メール</th><th class="text-end"></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($customer->users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('admin.customers.users.destroy', [$customer, $user]) }}" class="d-inline" onsubmit="return confirm('本当に {{ $user->email }} を削除しますか?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="bi bi-trash"></i> 削除
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <hr>

            <h6 class="mt-4 mb-3"><i class="bi bi-person-plus me-1"></i> ユーザを追加</h6>
            <form method="POST" action="{{ route('admin.customers.users.store', $customer) }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">名前</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                        @error('name')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">メールアドレス</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                        @error('email')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-lg"></i> 追加
                        </button>
                    </div>
                </div>
                <p class="text-muted mt-2 mb-0" style="font-size: 12px;">パスワードは自動生成され、追加完了画面で 1 度だけ表示されます。</p>
            </form>
        </div>
    </div>
@endsection
