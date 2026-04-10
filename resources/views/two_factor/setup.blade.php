@extends(auth()->user()->isMaster() ? 'layouts.admin' : 'layouts.tenant')

@section('title', '2 段階認証 (2FA) | Packto Console')

@section('content')
    <h4 class="c-grey-900 mT-10 mB-30">2 段階認証 (2FA)</h4>

    {{-- Recovery codes just generated --}}
    @if (session('2fa.recovery_codes_just_generated'))
        <div class="bgc-white bd bdrs-3 p-20 mB-20" style="border-left: 4px solid #f59e0b;">
            <h5 class="c-grey-900 mB-15"><i class="fa fa-triangle-exclamation c-orange-500 mR-5"></i> リカバリーコード (この画面でのみ表示)</h5>
            <p class="fsz-sm c-grey-600 mB-15">Authenticator にアクセスできなくなった場合にこのコードでログインできます。印刷するかパスワードマネージャに保存してください。各コードは 1 回限り。</p>
            <div class="bgc-white bdrs-3 p-15">
                @foreach (session('2fa.recovery_codes_just_generated') as $code)
                    <code class="d-b" style="font-size: 1rem; letter-spacing: .05em;">{{ $code }}</code>
                @endforeach
            </div>
        </div>
    @endif

    @if (session('status'))
        <div class="alert alert-success mB-20">{{ session('status') }}</div>
    @endif

    <div class="bgc-white bd bdrs-3 p-20 mB-20" style="max-width: 560px;">
        @if ($enabled)
            <h5 class="fw-600"><i class="fa fa-check-circle c-green-500 mR-5"></i> 2FA は有効です</h5>
            <p class="c-grey-600 fsz-sm">次回ログイン時に Authenticator の 6 桁コードが必要になります。</p>

            <hr>
            <h6 class="fw-600">リカバリーコード再生成</h6>
            <form method="POST" action="{{ route('two-factor.recovery-codes') }}" class="mB-20">
                @csrf
                <p class="c-grey-600 fsz-sm">既存のリカバリーコードを破棄して新しい 6 個を発行します。</p>
                <button type="submit" class="btn btn-sm btn-outline-secondary">再生成</button>
            </form>

            <hr>
            <h6 class="fw-600 c-red-500">2FA を無効化</h6>
            <form method="POST" action="{{ route('two-factor.disable') }}">
                @csrf
                <div class="mB-15" style="max-width: 300px;">
                    <label class="form-label">現在のパスワード</label>
                    <input type="password" name="current_password" class="form-control form-control-sm @error('current_password') is-invalid @enderror" required>
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-sm btn-danger">2FA を無効化</button>
            </form>

        @elseif ($qrDataUri && $pendingSecret)
            <h5 class="fw-600 mB-15">セットアップ</h5>
            <ol class="c-grey-600 fsz-sm" style="line-height: 1.8;">
                <li>Google Authenticator / Authy / 1Password などの TOTP アプリを開く</li>
                <li>下記 QR コードをスキャン (もしくは Secret を手入力)</li>
                <li>表示された 6 桁コードを入力して「有効化」</li>
            </ol>
            <div class="ta-c p-15 bgc-grey-100 bdrs-3 mB-15">
                <img src="{{ $qrDataUri }}" alt="2FA QR" style="display: inline-block;">
            </div>
            <p class="ta-c fsz-sm c-grey-600 mB-15">
                Secret: <code class="user-select-all">{{ $pendingSecret }}</code>
            </p>
            <form method="POST" action="{{ route('two-factor.confirm') }}">
                @csrf
                <div class="mB-15" style="max-width: 200px; margin: 0 auto;">
                    <label class="form-label">Authenticator の 6 桁コード</label>
                    <input type="text" name="code" class="form-control ta-c @error('code') is-invalid @enderror" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="ta-c mT-15">
                    <button type="submit" class="btn btn-primary">有効化</button>
                </div>
            </form>

        @else
            <h5 class="fw-600">2FA は無効です</h5>
            <p class="c-grey-600 fsz-sm">アカウントへの不正アクセスを防ぐため、有効化を推奨します (特に master ロールの場合)。</p>
            <form method="POST" action="{{ route('two-factor.setup') }}">
                @csrf
                <button type="submit" class="btn btn-primary">2FA を設定する</button>
            </form>
        @endif
    </div>
@endsection
