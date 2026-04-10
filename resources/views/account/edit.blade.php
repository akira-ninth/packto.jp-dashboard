@extends(auth()->user()->isMaster() ? 'layouts.admin' : 'layouts.tenant')

@section('title', 'アカウント設定 | Packto Console')

@section('content')
    <h1 class="h4 fw-bold mb-4">アカウント設定</h1>

    {{-- Basic info --}}
    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-person me-2"></i> 基本情報</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <tr><th style="width: 140px;">名前</th><td>{{ auth()->user()->name }}</td></tr>
                <tr><th>メール</th><td>{{ auth()->user()->email }}</td></tr>
                <tr><th>ロール</th><td><span class="badge bg-secondary">{{ auth()->user()->role }}</span></td></tr>
                @if (auth()->user()->customer)
                    <tr><th>顧客</th><td>{{ auth()->user()->customer->display_name }} ({{ auth()->user()->customer->subdomain }})</td></tr>
                @endif
            </table>
        </div>
    </div>

    {{-- 2FA section --}}
    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-shield-check me-2"></i> 2 段階認証 (2FA)</div>
        <div class="card-body">
            @if (auth()->user()->hasTwoFactorEnabled())
                <span class="badge badge-active"><i class="bi bi-check-circle me-1"></i>有効</span>
            @else
                <span class="badge badge-inactive"><i class="bi bi-x-circle me-1"></i>未設定</span>
            @endif
            <a href="{{ route('two-factor.show') }}" class="btn btn-sm btn-outline-primary ms-3">2FA 設定</a>
        </div>
    </div>

    {{-- Password change --}}
    <div class="card">
        <div class="card-header"><i class="bi bi-key me-2"></i> パスワード変更</div>
        <div class="card-body">
            <form method="POST" action="{{ route('account.password.update') }}" style="max-width: 400px;">
                @csrf
                @method('PATCH')
                <div class="mb-3">
                    <label class="form-label">現在のパスワード</label>
                    <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required autocomplete="current-password">
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">新しいパスワード (8 文字以上)</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">新しいパスワード (確認)</label>
                    <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary">変更する</button>
            </form>
        </div>
    </div>
@endsection
