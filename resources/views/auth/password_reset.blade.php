@extends('layouts.app')

@section('title', '新しいパスワードを設定 | Packto Console')

@section('content')
    <div class="text-center mb-4">
        <h1 class="h3 fw-bold mt-2 mb-2">新しいパスワードを設定</h1>
        <h2 class="h5 fw-medium text-muted mb-0">安全なパスワードを入力してください</h2>
    </div>

    <div class="row justify-content-center px-1">
        <div class="col-sm-8 col-md-6 col-xl-4">
            <form method="POST" action="{{ url('/password/reset') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="form-floating mb-4">
                    <input type="email" name="email" value="{{ old('email', $email) }}" class="form-control @error('email') is-invalid @enderror" id="resetEmail" placeholder="メールアドレス" required>
                    <label class="form-label" for="resetEmail">メールアドレス</label>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-floating mb-4">
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="resetPassword" placeholder="新しいパスワード" required autocomplete="new-password" autofocus>
                    <label class="form-label" for="resetPassword">新しいパスワード (8 文字以上)</label>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-floating mb-4">
                    <input type="password" name="password_confirmation" class="form-control" id="resetPasswordConfirm" placeholder="パスワード確認" required autocomplete="new-password">
                    <label class="form-label" for="resetPasswordConfirm">新しいパスワード (確認)</label>
                </div>
                <button type="submit" class="btn btn-lg btn-alt-primary w-100 py-3 fw-semibold">
                    <i class="fa fa-key me-1"></i> パスワードを更新
                </button>
            </form>
        </div>
    </div>
@endsection
