@extends('layouts.app')

@section('title', 'アカウント設定 | Packto Console')

@section('content')
    <h1>アカウント設定</h1>

    <div class="card" style="max-width: 540px;">
        <h2>基本情報</h2>
        <table>
            <tr><th style="width: 140px;">名前</th><td>{{ auth()->user()->name }}</td></tr>
            <tr><th>メール</th><td>{{ auth()->user()->email }}</td></tr>
            <tr><th>ロール</th><td>{{ auth()->user()->role }}</td></tr>
            @if (auth()->user()->customer)
                <tr><th>顧客</th><td>{{ auth()->user()->customer->display_name }} ({{ auth()->user()->customer->subdomain }})</td></tr>
            @endif
        </table>
    </div>

    <div class="card" style="max-width: 540px;">
        <h2>パスワード変更</h2>
        <form method="POST" action="{{ route('account.password.update') }}">
            @csrf
            @method('PATCH')

            <label>現在のパスワード</label>
            <input type="password" name="current_password" required autocomplete="current-password">
            @error('current_password')<div class="errors">{{ $message }}</div>@enderror

            <label>新しいパスワード (8 文字以上)</label>
            <input type="password" name="password" required autocomplete="new-password">
            @error('password')<div class="errors">{{ $message }}</div>@enderror

            <label>新しいパスワード (確認)</label>
            <input type="password" name="password_confirmation" required autocomplete="new-password">

            <p style="margin-top: 24px;">
                <button type="submit" class="btn" style="border: none; cursor: pointer;">変更する</button>
            </p>
        </form>
    </div>
@endsection
