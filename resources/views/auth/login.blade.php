@extends('layouts.app')

@section('title', 'ログイン | Packto Console')

@section('content')
    <h1 style="text-align: center;">Packto Console ログイン</h1>

    <div class="card" style="max-width: 420px; margin: 24px auto;">
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
        </form>
    </div>
@endsection
