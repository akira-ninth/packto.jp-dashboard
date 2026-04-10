@extends('layouts.app')

@section('title', 'ログイン | Packto Console')

@section('auth-heading')
    <h1>ようこそ</h1>
    <h2>ログインしてください</h2>
@endsection

@section('content')
    @if (session('status'))
        <div class="alert alert-success fs-sm mb-3">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ url('/login') }}">
        @csrf
        <div class="form-floating mb-3">
            <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" id="loginEmail" placeholder="メールアドレス" required autofocus>
            <label for="loginEmail">メールアドレス</label>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-floating mb-3">
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="loginPassword" placeholder="パスワード" required>
            <label for="loginPassword">パスワード</label>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-check mb-4">
            <input type="checkbox" name="remember" value="1" class="form-check-input" id="remember">
            <label for="remember" class="form-check-label fs-sm">ログイン状態を保持する</label>
        </div>
        <button type="submit" class="btn btn-lg btn-alt-primary w-100 py-2 fw-semibold">
            <i class="fa fa-right-to-bracket me-1"></i> ログイン
        </button>
        <p class="text-center mt-3 mb-0">
            <a href="{{ route('password.forgot') }}" class="fs-sm text-muted">パスワードを忘れた方はこちら</a>
        </p>
    </form>
@endsection
