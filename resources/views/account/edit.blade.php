@extends(auth()->user()->isMaster() ? 'layouts.admin' : 'layouts.tenant')

@section('title', 'アカウント設定 | Packto Console')

@section('content')
    <h4 class="c-grey-900 mT-10 mB-30">アカウント設定</h4>

    {{-- Basic info --}}
    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <h4 class="c-grey-900 mB-20"><i class="ti-user mR-10 c-grey-500"></i>基本情報</h4>
        <table class="table">
            <tr><th style="width: 140px;">名前</th><td>{{ auth()->user()->name }}</td></tr>
            <tr><th>メール</th><td>{{ auth()->user()->email }}</td></tr>
            <tr><th>ロール</th><td><span class="badge bg-secondary">{{ auth()->user()->role }}</span></td></tr>
            @if (auth()->user()->customer)
                <tr><th>顧客</th><td>{{ auth()->user()->customer->display_name }} ({{ auth()->user()->customer->subdomain }})</td></tr>
            @endif
        </table>
    </div>

    {{-- 2FA section --}}
    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <h4 class="c-grey-900 mB-20"><i class="ti-lock mR-10 c-grey-500"></i>2 段階認証 (2FA)</h4>
        @if (auth()->user()->hasTwoFactorEnabled())
            <span class="badge badge-active"><i class="fa fa-check-circle mR-5"></i>有効</span>
        @else
            <span class="badge badge-inactive"><i class="fa fa-times-circle mR-5"></i>未設定</span>
        @endif
        <a href="{{ route('two-factor.show') }}" class="btn btn-sm btn-primary mL-15">2FA 設定</a>
    </div>

    {{-- Password change --}}
    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <h4 class="c-grey-900 mB-20"><i class="ti-key mR-10 c-grey-500"></i>パスワード変更</h4>
        <form method="POST" action="{{ route('account.password.update') }}" style="max-width: 400px;">
            @csrf
            @method('PATCH')
            <div class="mB-20">
                <label class="form-label">現在のパスワード</label>
                <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required autocomplete="current-password">
                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mB-20">
                <label class="form-label">新しいパスワード (8 文字以上)</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mB-20">
                <label class="form-label">新しいパスワード (確認)</label>
                <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary">変更する</button>
        </form>
    </div>
@endsection
