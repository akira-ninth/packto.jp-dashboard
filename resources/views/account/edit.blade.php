@extends(auth()->user()->isMaster() ? 'layouts.admin' : 'layouts.tenant')

@section('title', 'アカウント設定 | Packto Console')

@section('content')
    <h1 class="h4 fw-bold mb-4">アカウント設定</h1>

    {{-- Basic info --}}
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title"><i class="fa fa-user me-2 opacity-50"></i>基本情報</h3>
        </div>
        <div class="block-content p-0">
            <table class="table table-vcenter mb-0">
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
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title"><i class="fa fa-shield-check me-2 opacity-50"></i>2 段階認証 (2FA)</h3>
        </div>
        <div class="block-content">
            @if (auth()->user()->hasTwoFactorEnabled())
                <span class="badge badge-active"><i class="fa fa-check-circle me-1"></i>有効</span>
            @else
                <span class="badge badge-inactive"><i class="fa fa-times-circle me-1"></i>未設定</span>
            @endif
            <a href="{{ route('two-factor.show') }}" class="btn btn-sm btn-alt-primary ms-3">2FA 設定</a>
        </div>
    </div>

    {{-- Password change --}}
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title"><i class="fa fa-key me-2 opacity-50"></i>パスワード変更</h3>
        </div>
        <div class="block-content">
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
                <button type="submit" class="btn btn-alt-primary">変更する</button>
            </form>
        </div>
    </div>
@endsection
