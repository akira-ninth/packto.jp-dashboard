@extends('layouts.app')

@section('title', 'パスワードリセット | Packto Console')

@section('content')
    <div class="card">
        <div class="card-body p-4">
            <h1 class="h5 fw-bold text-center mb-3">パスワードリセット</h1>

            @if (session('status'))
                <div class="alert alert-success small">{{ session('status') }}</div>
            @endif

            <p class="text-muted small mb-3">登録メールアドレスを入力すると、リセット用のリンクを送信します。</p>

            <form method="POST" action="{{ url('/password/forgot') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">メールアドレス</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary w-100">リセットリンクを送信</button>
                <p class="text-center mt-3 mb-0">
                    <a href="{{ route('login') }}" class="text-muted small">ログインに戻る</a>
                </p>
            </form>
        </div>
    </div>
@endsection
