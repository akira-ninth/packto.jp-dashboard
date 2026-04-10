@extends('layouts.app')

@section('title', 'パスワードリセット | Packto Console')

@section('content')
    <h4 class="fw-300 c-grey-900 mB-40">パスワードリセット</h4>
    <p class="c-grey-600 fsz-sm mB-30">登録メールアドレスにリセットリンクを送信します</p>

    @if (session('status'))
        <div class="alert alert-success fsz-sm mB-20">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ url('/password/forgot') }}">
        @csrf
        <div class="mB-20">
            <label class="form-label" for="resetEmail">メールアドレス</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" id="resetEmail" required autofocus>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-600 pY-10">
            リセットリンクを送信
        </button>
        <p class="ta-c mT-20 mB-0">
            <a href="{{ route('login') }}" class="fsz-sm c-grey-600">ログインに戻る</a>
        </p>
    </form>
@endsection
