@extends('layouts.admin')

@section('title', '顧客追加 | Packto Console')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0">顧客追加</h1>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> 一覧に戻る
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.customers.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">サブドメイン (英小文字・数字・ハイフン)</label>
                    <input type="text" name="subdomain" value="{{ old('subdomain') }}" class="form-control" required>
                    @error('subdomain')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror
                    <div class="form-text">→ <code>{value}.packto.jp</code></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">表示名</label>
                    <input type="text" name="display_name" value="{{ old('display_name') }}" class="form-control" required>
                    @error('display_name')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Origin URL</label>
                    <input type="url" name="origin_url" value="{{ old('origin_url') }}" placeholder="https://example.com" class="form-control" required>
                    @error('origin_url')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">プラン</label>
                    <select name="plan_id" class="form-select" required>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                    @error('plan_id')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror
                </div>

                <fieldset class="border rounded p-3 mt-4">
                    <legend class="float-none w-auto px-2 fs-6 fw-semibold">初期ユーザ (任意)</legend>
                    <p class="text-muted mb-3" style="font-size: 12px;">
                        顧客が <code>app.packto.jp</code> にログインするための customer ロールユーザを同時に作成します。
                        パスワードは自動生成され、作成完了画面で 1 度だけ表示されます。
                    </p>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="create_user" value="1" class="form-check-input" id="createUserCheck" @checked(old('create_user'))>
                        <label class="form-check-label" for="createUserCheck">初期ユーザを作成する</label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ユーザ名</label>
                        <input type="text" name="user_name" value="{{ old('user_name') }}" class="form-control">
                        @error('user_name')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">メールアドレス</label>
                        <input type="email" name="user_email" value="{{ old('user_email') }}" class="form-control">
                        @error('user_email')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror
                    </div>
                </fieldset>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> 作成
                    </button>
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary ms-2">キャンセル</a>
                </div>
            </form>
        </div>
    </div>
@endsection
