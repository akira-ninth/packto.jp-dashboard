@extends('layouts.app')

@section('title', 'パスワードリセット | Packto Console')

@section('content')
    <div class="text-center mb-4">
        <h1 class="h3 fw-bold mt-2 mb-2">パスワードリセット</h1>
        <h2 class="h5 fw-medium text-muted mb-0">登録メールアドレスにリセットリンクを送信します</h2>
    </div>

    <div class="row justify-content-center px-1">
        <div class="col-sm-8 col-md-6 col-xl-4">
            @if (session('status'))
                <div class="alert alert-success fs-sm">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ url('/password/forgot') }}">
                @csrf
                <div class="form-floating mb-4">
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" id="resetEmail" placeholder="メールアドレス" required autofocus>
                    <label class="form-label" for="resetEmail">メールアドレス</label>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-lg btn-alt-primary w-100 py-3 fw-semibold">
                    <i class="fa fa-paper-plane me-1"></i> リセットリンクを送信
                </button>
                <p class="text-center mt-4 mb-0">
                    <a href="{{ route('login') }}" class="fs-sm text-muted">ログインに戻る</a>
                </p>
            </form>
        </div>
    </div>
@endsection
