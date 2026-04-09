@extends('layouts.app')

@section('title', 'ログイン | Packto Console')

@section('content')
    <h1 style="text-align: center;">Packto Console ログイン</h1>

    <div class="card" style="max-width: 420px; margin: 24px auto;">
        @if (session('status'))
            <p style="background: #d1fae5; color: #065f46; padding: 12px; border-radius: 6px; font-size: 13px; margin-bottom: 16px;">
                {{ session('status') }}
            </p>
        @endif

        <form method="POST" action="{{ url('/login') }}">
            @csrf

            <label>メールアドレス</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus>
            @error('email')<div class="errors">{{ $message }}</div>@enderror

            <label>パスワード</label>
            <input type="password" name="password" required>
            @error('password')<div class="errors">{{ $message }}</div>@enderror

            <label style="font-weight: normal; margin-top: 16px;">
                <input type="checkbox" name="remember" value="1"> ログイン状態を保持する
            </label>

            <p style="margin-top: 24px;">
                <button type="submit" class="btn" style="border: none; cursor: pointer; width: 100%;">ログイン</button>
            </p>
            <p style="text-align: center; margin-top: 16px;">
                <a href="{{ route('password.forgot') }}" style="color: #6b7280; font-size: 13px;">パスワードを忘れた方はこちら</a>
            </p>
        </form>
    </div>
@endsection
