@extends('layouts.app')

@section('title', '新しいパスワードを設定 | Packto Console')

@section('auth-heading')
    <h1>パスワード再設定</h1>
    <h2>新しいパスワードを入力してください</h2>
@endsection

@section('content')
    <form method="POST" action="{{ url('/password/reset') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="form-floating mb-3">
            <input type="email" name="email" value="{{ old('email', $email) }}" class="form-control @error('email') is-invalid @enderror" id="resetEmail" placeholder="メールアドレス" required>
            <label for="resetEmail">メールアドレス</label>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-floating mb-3">
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="resetPassword" placeholder="新しいパスワード" required autocomplete="new-password" autofocus>
            <label for="resetPassword">新しいパスワード (8 文字以上)</label>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-floating mb-4">
            <input type="password" name="password_confirmation" class="form-control" id="resetPasswordConfirm" placeholder="パスワード確認" required autocomplete="new-password">
            <label for="resetPasswordConfirm">新しいパスワード (確認)</label>
        </div>
        <button type="submit" class="btn btn-lg btn-alt-primary w-100 py-2 fw-semibold">
            <i class="fa fa-key me-1"></i> パスワードを更新
        </button>
    </form>
@endsection
