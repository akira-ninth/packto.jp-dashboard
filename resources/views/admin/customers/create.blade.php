@extends('layouts.admin')

@section('title', '顧客追加 | Packto Console')

@section('content')
    <div class="peers ai-c jc-sb fxw-nw mT-10 mB-30">
        <div class="peer">
            <h4 class="c-grey-900">顧客追加</h4>
        </div>
        <div class="peer">
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left mR-5"></i> 一覧に戻る
            </a>
        </div>
    </div>

    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <h4 class="c-grey-900 mB-20">新規顧客情報</h4>
        <form method="POST" action="{{ route('admin.customers.store') }}">
            @csrf

            <div class="mB-20">
                <label class="form-label">サブドメイン (英小文字・数字・ハイフン)</label>
                <input type="text" name="subdomain" value="{{ old('subdomain') }}" class="form-control" required>
                @error('subdomain')<div class="c-red-500 mT-5 fsz-sm">{{ $message }}</div>@enderror
                <div class="form-text">→ <code>{value}.packto.jp</code></div>
            </div>

            <div class="mB-20">
                <label class="form-label">表示名</label>
                <input type="text" name="display_name" value="{{ old('display_name') }}" class="form-control" required>
                @error('display_name')<div class="c-red-500 mT-5 fsz-sm">{{ $message }}</div>@enderror
            </div>

            <div class="mB-20">
                <label class="form-label">Origin URL</label>
                <input type="url" name="origin_url" value="{{ old('origin_url') }}" placeholder="https://example.com" class="form-control" required>
                @error('origin_url')<div class="c-red-500 mT-5 fsz-sm">{{ $message }}</div>@enderror
            </div>

            <div class="mB-20">
                <label class="form-label">プラン</label>
                <select name="plan_id" class="form-select" required>
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>{{ $plan->name }}</option>
                    @endforeach
                </select>
                @error('plan_id')<div class="c-red-500 mT-5 fsz-sm">{{ $message }}</div>@enderror
            </div>

            <div class="bgc-grey-100 bd bdrs-3 p-20 mB-20">
                <h6 class="fw-600 mB-15">初期ユーザ (任意)</h6>
                <p class="c-grey-600 mB-15" style="font-size: .75rem;">
                    顧客が <code>app.packto.jp</code> にログインするための customer ロールユーザを同時に作成します。
                    パスワードは自動生成され、作成完了画面で 1 度だけ表示されます。
                </p>

                <div class="form-check mB-15">
                    <input type="checkbox" name="create_user" value="1" class="form-check-input" id="createUserCheck" @checked(old('create_user'))>
                    <label class="form-check-label" for="createUserCheck">初期ユーザを作成する</label>
                </div>

                <div class="mB-15">
                    <label class="form-label">ユーザ名</label>
                    <input type="text" name="user_name" value="{{ old('user_name') }}" class="form-control">
                    @error('user_name')<div class="c-red-500 mT-5 fsz-sm">{{ $message }}</div>@enderror
                </div>

                <div class="mB-15">
                    <label class="form-label">メールアドレス</label>
                    <input type="email" name="user_email" value="{{ old('user_email') }}" class="form-control">
                    @error('user_email')<div class="c-red-500 mT-5 fsz-sm">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mT-20">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-plus mR-5"></i> 作成
                </button>
                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary mL-10">キャンセル</a>
            </div>
        </form>
    </div>
@endsection
