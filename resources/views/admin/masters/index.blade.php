@extends('layouts.app')

@section('title', 'マスター管理 | Packto Console')

@section('nav')
    <nav>
        <a href="{{ route('admin.dashboard') }}">ダッシュボード</a>
        <a href="{{ route('admin.customers.index') }}">顧客一覧</a>
        <a href="{{ route('admin.masters.index') }}">マスター</a>
    </nav>
@endsection

@section('content')
    <h1>マスターアカウント</h1>

    @if (session('temp_credentials'))
        <div class="card" style="background: #fef3c7; border: 1px solid #f59e0b;">
            <h2 style="margin-top: 0;">⚠️ 初期ログイン情報 (この画面でのみ表示されます)</h2>
            <p style="font-size: 13px; color: #78350f;">
                控えてください。次にこのページをリロードすると消えます。
                追加されたマスターは初回ログイン後に <code>/account</code> でパスワードを変更してください。
            </p>
            <table>
                <tr><th style="width: 140px;">ログイン URL</th><td><code>https://admin.packto.jp/login</code></td></tr>
                <tr><th>メール</th><td><code>{{ session('temp_credentials.email') }}</code></td></tr>
                <tr><th>パスワード</th><td><code style="background: #fff; padding: 4px 8px; border-radius: 4px; font-size: 14px;">{{ session('temp_credentials.password') }}</code></td></tr>
            </table>
        </div>
    @endif

    <div class="card">
        <h2>登録済みマスター ({{ $masters->count() }})</h2>
        <table>
            <thead><tr><th>名前</th><th>メール</th><th>作成日</th><th></th></tr></thead>
            <tbody>
                @foreach ($masters as $master)
                    <tr>
                        <td>{{ $master->name }}</td>
                        <td>{{ $master->email }}</td>
                        <td>{{ $master->created_at?->format('Y-m-d') }}</td>
                        <td style="text-align: right;">
                            @if ($master->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.masters.destroy', $master) }}" style="display: inline; margin: 0;" onsubmit="return confirm('本当に {{ $master->email }} を削除しますか?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn danger" style="border: none; cursor: pointer; font-size: 12px; padding: 4px 10px;">削除</button>
                                </form>
                            @else
                                <span style="font-size: 12px; color: #6b7280;">(自分)</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>マスター追加</h2>
        <form method="POST" action="{{ route('admin.masters.store') }}">
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
