@extends(auth()->user()->isMaster() ? 'layouts.admin' : 'layouts.tenant')

@section('title', 'アカウント設定 | Packto Console')

@section('content')
    <h4 class="c-grey-900 mT-10 mB-30">アカウント設定</h4>

    @if (session('status'))
        <div class="alert alert-success mB-20">{{ session('status') }}</div>
    @endif

    {{-- Basic info --}}
    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <h4 class="c-grey-900 mB-20"><i class="ti-user mR-10 c-grey-500"></i>基本情報</h4>
        <table class="table">
            <tr><th style="width: 140px;">名前</th><td>{{ auth()->user()->name }}</td></tr>
            <tr><th>メール</th><td>{{ auth()->user()->email }}</td></tr>
        </table>
    </div>

    {{-- Email change --}}
    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <h4 class="c-grey-900 mB-20"><i class="ti-email mR-10 c-grey-500"></i>メールアドレス変更</h4>
        <form method="POST" action="{{ route('account.email.update') }}" style="max-width: 400px;">
            @csrf
            @method('PATCH')
            <div class="mB-20">
                <label class="form-label">新しいメールアドレス</label>
                <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mB-20">
                <label class="form-label">現在のパスワード (確認)</label>
                <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-primary">メールアドレスを変更</button>
        </form>
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
