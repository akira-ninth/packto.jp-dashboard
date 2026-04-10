@extends('layouts.app')

@section('title', '2 段階認証 | Packto Console')

@section('auth-heading')
    <h1>2 段階認証</h1>
    <h2>Authenticator アプリのコードを入力してください</h2>
@endsection

@section('content')
    <p class="text-muted fs-sm mb-3 text-center">6 桁コード、またはリカバリーコードを入力してください。</p>

    <form method="POST" action="{{ url('/two-factor/challenge') }}">
        @csrf
        <div class="form-floating mb-3">
            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" id="tfCode" placeholder="コード" inputmode="text" autocomplete="one-time-code" required autofocus>
            <label for="tfCode">コード</label>
            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-lg btn-alt-primary w-100 py-2 fw-semibold">
            <i class="fa fa-shield me-1"></i> 認証
        </button>
        <p class="text-center mt-3 mb-0">
            <a href="{{ route('login') }}" class="fs-sm text-muted">ログインに戻る</a>
        </p>
    </form>
@endsection
