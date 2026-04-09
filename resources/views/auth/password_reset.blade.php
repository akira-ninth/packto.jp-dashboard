@extends('layouts.app')

@section('title', '新しいパスワードを設定 | Packto Console')

@section('content')
    <h1 style="text-align: center;">新しいパスワードを設定</h1>

    <div class="card" style="max-width: 480px; margin: 24px auto;">
        <form method="POST" action="{{ url('/password/reset') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <label>メールアドレス</label>
            <input type="email" name="email" value="{{ old('email', $email) }}" required>
            @error('email')<div class="errors">{{ $message }}</div>@enderror

            <label>新しいパスワード (8 文字以上)</label>
            <input type="password" name="password" required autocomplete="new-password" autofocus>
            @error('password')<div class="errors">{{ $message }}</div>@enderror

            <label>新しいパスワード (確認)</label>
            <input type="password" name="password_confirmation" required autocomplete="new-password">

            <p style="margin-top: 24px;">
                <button type="submit" class="btn" style="border: none; cursor: pointer; width: 100%;">パスワードを更新</button>
            </p>
        </form>
    </div>
@endsection
