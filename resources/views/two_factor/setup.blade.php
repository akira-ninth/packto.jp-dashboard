@extends('layouts.app')

@section('title', '2 段階認証 (2FA) | Packto Console')

@section('content')
    <h1>2 段階認証 (2FA)</h1>

    @if (session('2fa.recovery_codes_just_generated'))
        <div class="card" style="background: #fef3c7; border: 1px solid #f59e0b;">
            <h2 style="margin-top: 0;">⚠️ リカバリーコード (この画面でのみ表示されます)</h2>
            <p style="font-size: 13px; color: #78350f;">
                Authenticator アプリにアクセスできなくなった場合に、6 桁コードの代わりにこのリカバリーコードでログインできます。
                印刷するかパスワードマネージャに保存してください。各コードは <strong>1 回しか使えません</strong>。
            </p>
            <div style="background: #fff; padding: 16px; border-radius: 6px; margin-top: 12px;">
                <table style="width: 100%; font-family: ui-monospace, Menlo, Monaco, monospace; font-size: 16px;">
                    @foreach (session('2fa.recovery_codes_just_generated') as $code)
                        <tr><td style="padding: 4px;">{{ $code }}</td></tr>
                    @endforeach
                </table>
            </div>
        </div>
    @endif

    <div class="card" style="max-width: 540px;">
        @if ($enabled)
            <h2>ステータス</h2>
            <p style="background: #d1fae5; color: #065f46; padding: 12px; border-radius: 6px; font-size: 13px;">
                ✅ 2FA は有効です。次回ログイン時に Authenticator の 6 桁コードが必要になります。
            </p>

            <h2 style="margin-top: 24px; font-size: 16px;">リカバリーコード再生成</h2>
            <form method="POST" action="{{ route('two-factor.recovery-codes') }}">
                @csrf
                <p style="font-size: 12px; color: #6b7280;">既存のリカバリーコードを破棄して新しい 6 個を発行します。</p>
                <button type="submit" class="btn secondary" style="border: none; cursor: pointer;">再生成</button>
            </form>

            <h2 style="margin-top: 24px; font-size: 16px;">2FA を無効化</h2>
            <form method="POST" action="{{ route('two-factor.disable') }}">
                @csrf
                <label>現在のパスワード</label>
                <input type="password" name="current_password" required>
                @error('current_password')<div class="errors">{{ $message }}</div>@enderror
                <p style="margin-top: 16px;">
                    <button type="submit" class="btn danger" style="border: none; cursor: pointer;">2FA を無効化</button>
                </p>
            </form>
        @elseif ($qrDataUri && $pendingSecret)
            <h2>セットアップ手順</h2>
            <ol style="font-size: 13px; line-height: 1.8;">
                <li>Google Authenticator / Authy / 1Password などの TOTP アプリを開く</li>
                <li>下記 QR コードをスキャン (もしくは secret を手入力)</li>
                <li>表示された 6 桁コードを下のフォームに入力して「有効化」</li>
            </ol>

            <div style="text-align: center; padding: 16px; background: #fff; border-radius: 6px;">
                <img src="{{ $qrDataUri }}" alt="2FA QR" style="display: inline-block;">
            </div>
            <p style="text-align: center; font-size: 12px; color: #6b7280;">
                Secret (手入力用): <code style="background: #f3f4f6; padding: 2px 6px; border-radius: 3px;">{{ $pendingSecret }}</code>
            </p>

            <form method="POST" action="{{ route('two-factor.confirm') }}" style="margin-top: 24px;">
                @csrf
                <label>Authenticator の 6 桁コード</label>
                <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus>
                @error('code')<div class="errors">{{ $message }}</div>@enderror
                <p style="margin-top: 16px;">
                    <button type="submit" class="btn" style="border: none; cursor: pointer;">有効化</button>
                </p>
            </form>
        @else
            <h2>ステータス</h2>
            <p style="font-size: 13px; color: #6b7280;">
                2FA は現在無効です。アカウントへの不正アクセスを防ぐため、有効化することを推奨します
                (特に master ロールの場合)。
            </p>
            <form method="POST" action="{{ route('two-factor.setup') }}" style="margin-top: 16px;">
                @csrf
                <button type="submit" class="btn" style="border: none; cursor: pointer;">2FA を設定する</button>
            </form>
        @endif
    </div>
@endsection
