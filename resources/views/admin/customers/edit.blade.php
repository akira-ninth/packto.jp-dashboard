@extends('layouts.app')

@section('title', '顧客編集 | Packto Console')

@section('nav')
    <nav>
        <a href="{{ route('admin.dashboard') }}">ダッシュボード</a>
        <a href="{{ route('admin.customers.index') }}">顧客一覧</a>
    </nav>
@endsection

@section('content')
    <h1>顧客編集 — {{ $customer->subdomain }}</h1>

    <div class="card">
        <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
            @csrf
            @method('PATCH')

            <label>サブドメイン (変更不可)</label>
            <input type="text" value="{{ $customer->subdomain }}.packto.jp" disabled>

            <label>表示名</label>
            <input type="text" name="display_name" value="{{ old('display_name', $customer->display_name) }}" required>
            @error('display_name')<div class="errors">{{ $message }}</div>@enderror

            <label>Origin URL</label>
            <input type="url" name="origin_url" value="{{ old('origin_url', $customer->origin_url) }}" required>
            @error('origin_url')<div class="errors">{{ $message }}</div>@enderror

            <label>プラン</label>
            <select name="plan_id" required>
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}" @selected(old('plan_id', $customer->plan_id) == $plan->id)>{{ $plan->name }}</option>
                @endforeach
            </select>
            @error('plan_id')<div class="errors">{{ $message }}</div>@enderror

            <label>
                <input type="checkbox" name="active" value="1" @checked(old('active', $customer->active))> 有効
            </label>

            <p style="margin-top: 24px;">
                <button type="submit" class="btn" style="border: none; cursor: pointer;">更新</button>
                <a href="{{ route('admin.customers.show', $customer) }}" class="btn secondary">キャンセル</a>
            </p>
        </form>
    </div>
@endsection
