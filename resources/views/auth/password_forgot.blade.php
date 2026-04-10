@extends('layouts.app')

@section('title', 'パスワードリセット | Packto Console')

@section('auth-heading')
    <h1>パスワードリセット</h1>
    <h2>登録メールアドレスにリセットリンクを送信します</h2>
@endsection

@section('content')
    @if (session('status'))
        <div class="alert alert-success fs-sm mb-3">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ url('/password/forgot') }}">
        @csrf
        <div class="form-floating mb-3">
            <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" id="resetEmail" placeholder="メールアドレス" required autofocus>
            <label for="resetEmail">メールアドレス</label>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-lg btn-alt-primary w-100 py-2 fw-semibold">
            <i class="fa fa-paper-plane me-1"></i> リセットリンクを送信
        </button>
        <p class="text-center mt-3 mb-0">
            <a href="{{ route('login') }}" class="fs-sm text-muted">ログインに戻る</a>
        </p>
    </form>
@endsection
