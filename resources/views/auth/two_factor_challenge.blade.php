@extends('layouts.app')

@section('title', '2 段階認証 | Packto Console')

@section('content')
    <h4 class="fw-300 c-grey-900 mB-40">2 段階認証</h4>
    <p class="c-grey-600 fsz-sm mB-30 ta-c">6 桁コード、またはリカバリーコードを入力してください。</p>

    <form method="POST" action="{{ url('/two-factor/challenge') }}">
        @csrf
        <div class="mB-20">
            <label class="form-label" for="tfCode">コード</label>
            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" id="tfCode" inputmode="text" autocomplete="one-time-code" required autofocus>
            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-600 pY-10">
            認証
        </button>
        <p class="ta-c mT-20 mB-0">
            <a href="{{ route('login') }}" class="fsz-sm c-grey-600">ログインに戻る</a>
        </p>
    </form>
@endsection
