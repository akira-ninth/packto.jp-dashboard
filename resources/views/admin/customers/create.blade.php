@extends('layouts.app')

@section('title', '顧客追加 | Packto Console')

@section('nav')
    <nav>
        <a href="{{ route('admin.dashboard') }}">ダッシュボード</a>
        <a href="{{ route('admin.customers.index') }}">顧客一覧</a>
        <a href="{{ route('admin.masters.index') }}">マスター</a>
    </nav>
@endsection

@section('content')
    <h1>顧客追加</h1>

    <div class="card">
        <form method="POST" action="{{ route('admin.customers.store') }}">
            @csrf
            <label>サブドメイン (英小文字・数字・ハイフン)</label>
            <input type="text" name="subdomain" value="{{ old('subdomain') }}" required>
            @error('subdomain')<div class="errors">{{ $message }}</div>@enderror
            <p style="font-size: 12px; color: #6b7280; margin: 4px 0 0;">→ <code>{value}.packto.jp</code></p>

            <label>表示名</label>
            <input type="text" name="display_name" value="{{ old('display_name') }}" required>
            @error('display_name')<div class="errors">{{ $message }}</div>@enderror

            <label>Origin URL</label>
            <input type="url" name="origin_url" value="{{ old('origin_url') }}" placeholder="https://example.com" required>
            @error('origin_url')<div class="errors">{{ $message }}</div>@enderror

            <label>プラン</label>
            <select name="plan_id" required>
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>{{ $plan->name }}</option>
                @endforeach
            </select>
            @error('plan_id')<div class="errors">{{ $message }}</div>@enderror

            <fieldset style="margin-top: 24px; padding: 16px; border: 1px solid #e5e7eb; border-radius: 6px;">
                <legend style="padding: 0 8px; font-weight: 600; font-size: 14px;">初期ユーザ (任意)</legend>
                <p style="font-size: 12px; color: #6b7280; margin: 4px 0 12px;">
                    顧客が <code>app.packto.jp</code> にログインするための customer ロールユーザを同時に作成します。
                    パスワードは自動生成され、作成完了画面で 1 度だけ表示されます。
                </p>

                <label style="font-weight: normal;">
                    <input type="checkbox" name="create_user" value="1" @checked(old('create_user'))> 初期ユーザを作成する
                </label>

                <label style="margin-top: 12px;">ユーザ名</label>
                <input type="text" name="user_name" value="{{ old('user_name') }}">
                @error('user_name')<div class="errors">{{ $message }}</div>@enderror

                <label>メールアドレス</label>
                <input type="email" name="user_email" value="{{ old('user_email') }}">
                @error('user_email')<div class="errors">{{ $message }}</div>@enderror
            </fieldset>

            <p style="margin-top: 24px;">
                <button type="submit" class="btn" style="border: none; cursor: pointer;">作成</button>
                <a href="{{ route('admin.customers.index') }}" class="btn secondary">キャンセル</a>
            </p>
        </form>
    </div>
@endsection
