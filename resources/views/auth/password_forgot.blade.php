@extends('layouts.app')

@section('title', 'パスワードリセット | Packto Console')

@section('content')
    <h1 style="text-align: center;">パスワードリセット</h1>

    <div class="card" style="max-width: 480px; margin: 24px auto;">
        @if (session('status'))
            <p style="background: #d1fae5; color: #065f46; padding: 12px; border-radius: 6px; font-size: 13px; margin-bottom: 16px;">
                {{ session('status') }}
            </p>
        @endif

        <p style="font-size: 13px; color: #6b7280; margin-bottom: 16px;">
            登録メールアドレスを入力すると、リセット用のリンクを送信します。
        </p>

        <form method="POST" action="{{ url('/password/forgot') }}">
            @csrf
            <label>メールアドレス</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus>
            @error('email')<div class="errors">{{ $message }}</div>@enderror

            <p style="margin-top: 24px;">
                <button type="submit" class="btn" style="border: none; cursor: pointer; width: 100%;">リセットリンクを送信</button>
            </p>
            <p style="text-align: center; margin-top: 16px;">
                <a href="{{ route('login') }}" style="color: #6b7280; font-size: 13px;">ログインに戻る</a>
            </p>
        </form>
    </div>
@endsection
