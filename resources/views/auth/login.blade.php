@extends('layouts.app')

@section('title', 'ログイン | Packto Console')

@section('content')
    <div class="card">
        <div class="card-body p-4">
            <h1 class="h5 fw-bold text-center mb-4">Packto Console ログイン</h1>

            @if (session('status'))
                <div class="alert alert-success small">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ url('/login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">メールアドレス</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">パスワード</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="remember" value="1" class="form-check-input" id="remember">
                    <label for="remember" class="form-check-label small">ログイン状態を保持する</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">ログイン</button>
                <p class="text-center mt-3 mb-0">
                    <a href="{{ route('password.forgot') }}" class="text-muted small">パスワードを忘れた方はこちら</a>
                </p>
            </form>
        </div>
    </div>
@endsection
