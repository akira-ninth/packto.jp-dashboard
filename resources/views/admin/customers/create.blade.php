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
        <form method="POST" action="{{ route('admin.customers.store') }}" id="createForm">
            @csrf

            <div class="mB-20">
                <label class="form-label">ワーカードメイン (英小文字・数字・ハイフン)</label>
                <div class="input-group" style="max-width: 400px;">
                    <input type="text" name="subdomain" id="subdomainInput" value="{{ old('subdomain') }}" class="form-control" required pattern="[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?" placeholder="example">
                    <span class="input-group-text">.packto.jp</span>
                </div>
                <div id="subdomainFeedback" class="mT-5 fsz-sm" style="display:none;"></div>
                @error('subdomain')<div class="c-red-500 mT-5 fsz-sm">{{ $message }}</div>@enderror
            </div>

            <div class="mB-20">
                <label class="form-label">サイト名</label>
                <input type="text" name="display_name" id="displayNameInput" value="{{ old('display_name') }}" class="form-control" required style="max-width: 400px;">
                <div id="displayNameFeedback" class="mT-5 fsz-sm" style="display:none;"></div>
                @error('display_name')<div class="c-red-500 mT-5 fsz-sm">{{ $message }}</div>@enderror
            </div>

            <div class="mB-20">
                <label class="form-label">対象URL</label>
                <input type="url" name="origin_url" value="{{ old('origin_url') }}" placeholder="https://example.com" class="form-control" required style="max-width: 500px;">
                @error('origin_url')<div class="c-red-500 mT-5 fsz-sm">{{ $message }}</div>@enderror
            </div>

            <div class="mB-20">
                <label class="form-label">プラン</label>
                <select name="plan_id" class="form-select" required style="max-width: 250px;">
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>{{ $plan->name }}</option>
                    @endforeach
                </select>
                @error('plan_id')<div class="c-red-500 mT-5 fsz-sm">{{ $message }}</div>@enderror
            </div>

            <div class="bgc-grey-100 bd bdrs-3 p-20 mB-20">
                <h6 class="fw-600 mB-15">初期ユーザ (任意)</h6>
                <p class="c-grey-600 mB-15" style="font-size: .75rem;">
                    顧客が <code>app.packto.jp</code> にログインするためのユーザを同時に作成します。
                    パスワードは自動生成され、作成完了画面で 1 度だけ表示されます。
                </p>

                <div class="form-check mB-15">
                    <input type="checkbox" name="create_user" value="1" class="form-check-input" id="createUserCheck" @checked(old('create_user'))>
                    <label class="form-check-label" for="createUserCheck">初期ユーザを作成する</label>
                </div>

                <div class="mB-15">
                    <label class="form-label">ユーザ名</label>
                    <input type="text" name="user_name" value="{{ old('user_name') }}" class="form-control" style="max-width: 400px;">
                    @error('user_name')<div class="c-red-500 mT-5 fsz-sm">{{ $message }}</div>@enderror
                </div>

                <div class="mB-15">
                    <label class="form-label">メールアドレス</label>
                    <input type="email" name="user_email" value="{{ old('user_email') }}" class="form-control" style="max-width: 400px;">
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

@section('scripts')
<script>
(function() {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const checkUrl = '{{ route("admin.customers.check-unique") }}';

    function checkUnique(field, input, feedbackEl) {
        const value = input.value.trim();
        if (!value) { feedbackEl.style.display = 'none'; return; }

        fetch(checkUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ field: field, value: value }),
        })
        .then(r => r.json())
        .then(data => {
            feedbackEl.style.display = 'block';
            if (data.available) {
                feedbackEl.innerHTML = '<span style="color:#059669;"><i class="fa fa-check-circle mR-5"></i>使用可能</span>';
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
            } else {
                feedbackEl.innerHTML = '<span style="color:#dc2626;"><i class="fa fa-times-circle mR-5"></i>既に使用されています</span>';
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
            }
        })
        .catch(() => { feedbackEl.style.display = 'none'; });
    }

    const subdomainInput = document.getElementById('subdomainInput');
    const displayNameInput = document.getElementById('displayNameInput');

    subdomainInput.addEventListener('blur', () => checkUnique('subdomain', subdomainInput, document.getElementById('subdomainFeedback')));
    displayNameInput.addEventListener('blur', () => checkUnique('display_name', displayNameInput, document.getElementById('displayNameFeedback')));
})();
</script>
@endsection
