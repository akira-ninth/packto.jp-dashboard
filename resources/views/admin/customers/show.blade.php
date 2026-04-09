@extends('layouts.app')

@section('title', $customer->display_name . ' | Packto Console')

@section('nav')
    <nav>
        <a href="{{ route('admin.dashboard') }}">ダッシュボード</a>
        <a href="{{ route('admin.customers.index') }}">顧客一覧</a>
        <a href="{{ route('admin.masters.index') }}">マスター</a>
        <a href="{{ route('admin.audit-logs.index') }}">監査ログ</a>
    </nav>
@endsection

@section('content')
    <h1>{{ $customer->display_name }}</h1>

    @if (session('temp_credentials'))
        @php $mailSent = session('temp_credentials.mail_sent'); @endphp
        <div class="card" style="background: #fef3c7; border: 1px solid #f59e0b;">
            <h2 style="margin-top: 0;">⚠️ 初期ログイン情報 (この画面でのみ表示されます)</h2>
            @if ($mailSent === true)
                <p style="font-size: 13px; color: #065f46; background: #d1fae5; padding: 8px 12px; border-radius: 4px;">
                    ✅ 招待メールを <code>{{ session('temp_credentials.email') }}</code> に送信しました。
                </p>
            @elseif ($mailSent === false)
                <p style="font-size: 13px; color: #991b1b; background: #fee2e2; padding: 8px 12px; border-radius: 4px;">
                    ⚠️ メール送信に失敗しました。下記の情報を手動で控えて顧客に伝えてください。
                </p>
            @endif
            <p style="font-size: 13px; color: #78350f;">
                次にこのページをリロードすると消えます。
                顧客は初回ログイン後に <code>/account</code> でパスワードを変更してください。
            </p>
            <table>
                <tr><th style="width: 140px;">ログイン URL</th><td><code>https://app.packto.jp/login</code></td></tr>
                <tr><th>メール</th><td><code>{{ session('temp_credentials.email') }}</code></td></tr>
                <tr><th>パスワード</th><td><code style="background: #fff; padding: 4px 8px; border-radius: 4px; font-size: 14px;">{{ session('temp_credentials.password') }}</code></td></tr>
            </table>
        </div>
    @endif

    <div class="card">
        <table>
            <tr><th style="width: 180px;">サブドメイン</th><td><code>{{ $customer->subdomain }}.packto.jp</code></td></tr>
            <tr><th>Origin URL</th><td><a href="{{ $customer->origin_url }}" target="_blank" rel="noopener">{{ $customer->origin_url }}</a></td></tr>
            <tr><th>プラン</th><td><span class="badge {{ $customer->plan->slug }}">{{ $customer->plan->name }}</span></td></tr>
            <tr><th>状態</th><td>
                @if ($customer->active)
                    <span class="badge active">有効</span>
                @else
                    <span class="badge inactive">停止</span>
                @endif
            </td></tr>
            <tr><th>作成日</th><td>{{ $customer->created_at?->format('Y-m-d H:i') }}</td></tr>
        </table>

        <p style="margin-top: 24px;">
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn">編集</a>
            <a href="{{ route('admin.customers.index') }}" class="btn secondary">一覧に戻る</a>
        </p>
    </div>

    <div class="card">
        <h2>使用量サマリ (直近 7 日)</h2>
        @php
            $sReqs = (int) ($usageSummary['reqs'] ?? 0);
            $sOut = (float) ($usageSummary['output_bytes'] ?? 0);
            $sIn = (float) ($usageSummary['input_bytes'] ?? 0);
            $sRatio = $sIn > 0 ? ($sOut / $sIn) * 100 : null;
        @endphp
        @if ($sReqs > 0)
            <div style="display: flex; gap: 32px; flex-wrap: wrap; margin-bottom: 16px;">
                <div>
                    <div style="font-size: 12px; color: #6b7280;">req</div>
                    <div style="font-size: 28px; font-weight: 700;">{{ number_format($sReqs) }}</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: #6b7280;">配信 MB</div>
                    <div style="font-size: 28px; font-weight: 700;">{{ number_format($sOut / 1024 / 1024, 2) }}</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: #6b7280;">origin MB</div>
                    <div style="font-size: 28px; font-weight: 700;">{{ number_format($sIn / 1024 / 1024, 2) }}</div>
                </div>
                @if ($sRatio !== null)
                    <div>
                        <div style="font-size: 12px; color: #6b7280;">圧縮率</div>
                        <div style="font-size: 28px; font-weight: 700; color: #059669;">{{ number_format($sRatio, 1) }}%</div>
                    </div>
                @endif
            </div>
            @if (! empty($usageByFormat))
                <table>
                    <thead><tr><th>format</th><th style="text-align: right;">req</th><th style="text-align: right;">origin MB</th><th style="text-align: right;">配信 MB</th><th style="text-align: right;">圧縮率</th></tr></thead>
                    <tbody>
                        @foreach ($usageByFormat as $row)
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
            @endif
        @else
            <p style="color: #6b7280;">直近 7 日のリクエストがありません</p>
        @endif
    </div>

    <div class="card">
        <h2>所属ユーザ ({{ $customer->users->count() }})</h2>
        @if ($customer->users->isEmpty())
            <p style="color: #6b7280;">ユーザが登録されていません</p>
        @else
            <table>
                <thead><tr><th>名前</th><th>メール</th><th></th></tr></thead>
                <tbody>
                    @foreach ($customer->users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td style="text-align: right;">
                                <form method="POST" action="{{ route('admin.customers.users.destroy', [$customer, $user]) }}" style="display: inline; margin: 0;" onsubmit="return confirm('本当に {{ $user->email }} を削除しますか?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn danger" style="border: none; cursor: pointer; font-size: 12px; padding: 4px 10px;">削除</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <h2 style="margin-top: 24px; font-size: 16px;">ユーザを追加</h2>
        <form method="POST" action="{{ route('admin.customers.users.store', $customer) }}">
            @csrf
            <label>名前</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
            @error('name')<div class="errors">{{ $message }}</div>@enderror

            <label>メールアドレス</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
            @error('email')<div class="errors">{{ $message }}</div>@enderror

            <p style="margin-top: 16px;">
                <button type="submit" class="btn" style="border: none; cursor: pointer;">追加</button>
            </p>
            <p style="font-size: 12px; color: #6b7280;">パスワードは自動生成され、追加完了画面で 1 度だけ表示されます。</p>
        </form>
    </div>
@endsection
