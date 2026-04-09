@extends('layouts.app')

@section('title', '2 段階認証 | Packto Console')

@section('content')
    <h1 style="text-align: center;">2 段階認証</h1>

    <div class="card" style="max-width: 420px; margin: 24px auto;">
        <p style="font-size: 13px; color: #6b7280; margin-bottom: 16px;">
            Authenticator アプリの 6 桁コードを入力してください。<br>
            アプリが使えない場合はリカバリーコードを入力できます。
        </p>

        <form method="POST" action="{{ url('/two-factor/challenge') }}">
            @csrf
            <label>コード</label>
            <input type="text" name="code" inputmode="text" autocomplete="one-time-code" required autofocus>
            @error('code')<div class="errors">{{ $message }}</div>@enderror

            <p style="margin-top: 24px;">
                <button type="submit" class="btn" style="border: none; cursor: pointer; width: 100%;">認証</button>
            </p>
            <p style="text-align: center; margin-top: 16px;">
                <a href="{{ route('login') }}" style="color: #6b7280; font-size: 13px;">ログインに戻る</a>
            </p>
        </form>
    </div>
@endsection
