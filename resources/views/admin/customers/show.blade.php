@extends('layouts.app')

@section('title', $customer->display_name . ' | Packto Console')

@section('nav')
    <nav>
        <a href="{{ route('admin.dashboard') }}">ダッシュボード</a>
        <a href="{{ route('admin.customers.index') }}">顧客一覧</a>
    </nav>
@endsection

@section('content')
    <h1>{{ $customer->display_name }}</h1>

    @if (session('temp_credentials'))
        <div class="card" style="background: #fef3c7; border: 1px solid #f59e0b;">
            <h2 style="margin-top: 0;">⚠️ 初期ログイン情報 (この画面でのみ表示されます)</h2>
            <p style="font-size: 13px; color: #78350f;">
                顧客に渡したら必ず控えてください。次にこの画面をリロードすると消えます。
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
        <h2>所属ユーザ ({{ $customer->users->count() }})</h2>
        @if ($customer->users->isEmpty())
            <p style="color: #6b7280;">ユーザが登録されていません</p>
        @else
            <table>
                <thead><tr><th>名前</th><th>メール</th><th>ロール</th></tr></thead>
                <tbody>
                    @foreach ($customer->users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
