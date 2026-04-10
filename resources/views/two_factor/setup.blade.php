@extends(auth()->user()->isMaster() ? 'layouts.admin' : 'layouts.tenant')

@section('title', '2 段階認証 (2FA) | Packto Console')

@section('content')
    <h1 class="h4 fw-bold mb-4">2 段階認証 (2FA)</h1>

    {{-- Recovery codes just generated --}}
    @if (session('2fa.recovery_codes_just_generated'))
        <div class="alert alert-warning">
            <h5 class="alert-heading"><i class="bi bi-exclamation-triangle me-1"></i> リカバリーコード (この画面でのみ表示)</h5>
            <p class="small mb-2">Authenticator にアクセスできなくなった場合にこのコードでログインできます。印刷するかパスワードマネージャに保存してください。各コードは 1 回限り。</p>
            <div class="bg-white rounded p-3">
                @foreach (session('2fa.recovery_codes_just_generated') as $code)
                    <code class="d-block" style="font-size: 16px; letter-spacing: .05em;">{{ $code }}</code>
                @endforeach
            </div>
        </div>
    @endif

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card" style="max-width: 560px;">
        <div class="card-body">
            @if ($enabled)
                <h5 class="fw-bold"><i class="bi bi-check-circle text-success me-1"></i> 2FA は有効です</h5>
                <p class="text-muted small">次回ログイン時に Authenticator の 6 桁コードが必要になります。</p>

                <hr>
                <h6 class="fw-bold">リカバリーコード再生成</h6>
                <form method="POST" action="{{ route('two-factor.recovery-codes') }}" class="mb-4">
                    @csrf
                    <p class="text-muted small">既存のリカバリーコードを破棄して新しい 6 個を発行します。</p>
                    <button type="submit" class="btn btn-sm btn-outline-secondary">再生成</button>
                </form>

                <hr>
                <h6 class="fw-bold text-danger">2FA を無効化</h6>
                <form method="POST" action="{{ route('two-factor.disable') }}">
                    @csrf
                    <div class="mb-3" style="max-width: 300px;">
                        <label class="form-label">現在のパスワード</label>
                        <input type="password" name="current_password" class="form-control form-control-sm @error('current_password') is-invalid @enderror" required>
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-sm btn-danger">2FA を無効化</button>
                </form>

            @elseif ($qrDataUri && $pendingSecret)
                <h5 class="fw-bold mb-3">セットアップ</h5>
                <ol class="text-muted small" style="line-height: 1.8;">
                    <li>Google Authenticator / Authy / 1Password などの TOTP アプリを開く</li>
                    <li>下記 QR コードをスキャン (もしくは Secret を手入力)</li>
                    <li>表示された 6 桁コードを入力して「有効化」</li>
                </ol>
                <div class="text-center p-3 bg-light rounded mb-3">
                    <img src="{{ $qrDataUri }}" alt="2FA QR" style="display: inline-block;">
                </div>
                <p class="text-center small text-muted mb-3">
                    Secret: <code class="user-select-all">{{ $pendingSecret }}</code>
                </p>
                <form method="POST" action="{{ route('two-factor.confirm') }}">
                    @csrf
                    <div class="mb-3" style="max-width: 200px; margin: 0 auto;">
                        <label class="form-label">Authenticator の 6 桁コード</label>
                        <input type="text" name="code" class="form-control text-center @error('code') is-invalid @enderror" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-primary">有効化</button>
                    </div>
                </form>

            @else
                <h5 class="fw-bold">2FA は無効です</h5>
                <p class="text-muted small">アカウントへの不正アクセスを防ぐため、有効化を推奨します (特に master ロールの場合)。</p>
                <form method="POST" action="{{ route('two-factor.setup') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">2FA を設定する</button>
                </form>
            @endif
        </div>
    </div>
@endsection
