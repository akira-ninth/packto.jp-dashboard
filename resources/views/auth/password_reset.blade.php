@extends('layouts.app')

@section('title', '新しいパスワードを設定 | Packto Console')

@section('content')
    <h4 class="fw-300 c-grey-900 mB-40">パスワード再設定</h4>
    <p class="c-grey-600 fsz-sm mB-30">新しいパスワードを入力してください</p>

    <form method="POST" action="{{ url('/password/reset') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="mB-20">
            <label class="form-label" for="resetEmail">メールアドレス</label>
            <input type="email" name="email" value="{{ old('email', $email) }}" class="form-control @error('email') is-invalid @enderror" id="resetEmail" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mB-20">
            <label class="form-label" for="resetPassword">新しいパスワード (8 文字以上)</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="resetPassword" required autocomplete="new-password" autofocus>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mB-30">
            <label class="form-label" for="resetPasswordConfirm">新しいパスワード (確認)</label>
            <input type="password" name="password_confirmation" class="form-control" id="resetPasswordConfirm" required autocomplete="new-password">
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-600 pY-10">
            パスワードを更新
        </button>
    </form>
@endsection
