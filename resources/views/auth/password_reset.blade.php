@extends('layouts.app')

@section('title', '新しいパスワードを設定 | Packto Console')

@section('content')
    <div class="card">
        <div class="card-body p-4">
            <h1 class="h5 fw-bold text-center mb-4">新しいパスワードを設定</h1>

            <form method="POST" action="{{ url('/password/reset') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="mb-3">
                    <label class="form-label">メールアドレス</label>
                    <input type="email" name="email" value="{{ old('email', $email) }}" class="form-control @error('email') is-invalid @enderror" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">新しいパスワード (8 文字以上)</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password" autofocus>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">新しいパスワード (確認)</label>
                    <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary w-100">パスワードを更新</button>
            </form>
        </div>
    </div>
@endsection
