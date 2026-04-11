@extends('layouts.app')

@section('title', 'ログイン | Packto Console')

@section('content')
    <h4 class="fw-300 c-grey-900 mB-40">ログイン</h4>

    @if (session('status'))
        <div class="alert alert-success fsz-sm mB-20">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ url('/login') }}">
        @csrf
        <div class="mB-20">
            <label class="form-label" for="loginEmail">メールアドレス</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" id="loginEmail" required autofocus>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mB-20">
            <label class="form-label" for="loginPassword">パスワード</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="loginPassword" required>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-check mB-30">
            <input type="checkbox" name="remember" value="1" class="form-check-input" id="remember">
            <label for="remember" class="form-check-label fsz-sm">ログイン状態を保持する</label>
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-600 pY-10">
            ログイン
        </button>
        <p class="ta-c mT-20 mB-0">
            <a href="{{ route('password.forgot') }}" class="fsz-sm c-grey-600">パスワードを忘れた方はこちら</a>
        </p>
    </form>
    <p class="ta-c mT-30 mB-0 d-n@sm+">
        <a href="https://packto.jp/" class="fsz-sm c-grey-600">← サービスサイトに戻る</a>
    </p>
@endsection
