@extends('layouts.app')

@section('title', '2 段階認証 | Packto Console')

@section('content')
    <div class="text-center mb-4">
        <h1 class="h3 fw-bold mt-2 mb-2">2 段階認証</h1>
        <h2 class="h5 fw-medium text-muted mb-0">Authenticator アプリのコードを入力してください</h2>
    </div>

    <div class="row justify-content-center px-1">
        <div class="col-sm-8 col-md-6 col-xl-4">
            <p class="text-muted fs-sm mb-4 text-center">6 桁コード、またはリカバリーコードを入力してください。</p>

            <form method="POST" action="{{ url('/two-factor/challenge') }}">
                @csrf
                <div class="form-floating mb-4">
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" id="tfCode" placeholder="コード" inputmode="text" autocomplete="one-time-code" required autofocus>
                    <label class="form-label" for="tfCode">コード</label>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-lg btn-alt-primary w-100 py-3 fw-semibold">
                    <i class="fa fa-shield-check me-1"></i> 認証
                </button>
                <p class="text-center mt-4 mb-0">
                    <a href="{{ route('login') }}" class="fs-sm text-muted">ログインに戻る</a>
                </p>
            </form>
        </div>
    </div>
@endsection
