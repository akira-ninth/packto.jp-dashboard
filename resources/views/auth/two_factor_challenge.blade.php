@extends('layouts.app')

@section('title', '2 段階認証 | Packto Console')

@section('content')
    <div class="card">
        <div class="card-body p-4">
            <h1 class="h5 fw-bold text-center mb-3">2 段階認証</h1>
            <p class="text-muted small mb-3">Authenticator アプリの 6 桁コード、またはリカバリーコードを入力してください。</p>

            <form method="POST" action="{{ url('/two-factor/challenge') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">コード</label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" inputmode="text" autocomplete="one-time-code" required autofocus>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary w-100">認証</button>
                <p class="text-center mt-3 mb-0">
                    <a href="{{ route('login') }}" class="text-muted small">ログインに戻る</a>
                </p>
            </form>
        </div>
    </div>
@endsection
