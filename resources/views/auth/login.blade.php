@extends('layouts.app')

@section('title', 'ログイン | Packto Console')

@section('content')
    <div class="text-center mb-4">
        <h1 class="h3 fw-bold mt-2 mb-2">ようこそ</h1>
        <h2 class="h5 fw-medium text-muted mb-0">ログインしてください</h2>
    </div>

    <div class="row justify-content-center px-1">
        <div class="col-sm-8 col-md-6 col-xl-4">
            @if (session('status'))
                <div class="alert alert-success fs-sm">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ url('/login') }}">
                @csrf
                <div class="form-floating mb-4">
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" id="loginEmail" placeholder="メールアドレス" required autofocus>
                    <label class="form-label" for="loginEmail">メールアドレス</label>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-floating mb-4">
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="loginPassword" placeholder="パスワード" required>
                    <label class="form-label" for="loginPassword">パスワード</label>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-check mb-4">
                    <input type="checkbox" name="remember" value="1" class="form-check-input" id="remember">
                    <label for="remember" class="form-check-label fs-sm">ログイン状態を保持する</label>
                </div>
                <button type="submit" class="btn btn-lg btn-alt-primary w-100 py-3 fw-semibold">
                    <i class="fa fa-right-to-bracket me-1"></i> ログイン
                </button>
                <p class="text-center mt-4 mb-0">
                    <a href="{{ route('password.forgot') }}" class="fs-sm text-muted">パスワードを忘れた方はこちら</a>
                </p>
            </form>
        </div>
    </div>
@endsection
