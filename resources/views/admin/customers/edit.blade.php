@extends('layouts.admin')

@section('title', '顧客編集 | Packto Console')

@section('content')
    <div class="peers ai-c jc-sb fxw-nw mT-10 mB-30">
        <div class="peer">
            <h4 class="c-grey-900">顧客編集 — {{ $customer->subdomain }}</h4>
        </div>
        <div class="peer">
            <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left mR-5"></i> 詳細に戻る
            </a>
        </div>
    </div>

    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <h4 class="c-grey-900 mB-20">顧客情報を編集</h4>
        <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
            @csrf
            @method('PATCH')

            <div class="mB-20">
                <label class="form-label">ワーカードメイン (変更不可)</label>
                <input type="text" value="{{ $customer->subdomain }}.packto.jp" class="form-control" disabled>
            </div>

            <div class="mB-20">
                <label class="form-label">サイト名</label>
                <input type="text" name="display_name" value="{{ old('display_name', $customer->display_name) }}" class="form-control" required>
                @error('display_name')<div class="c-red-500 mT-5 fsz-sm">{{ $message }}</div>@enderror
            </div>

            <div class="mB-20">
                <label class="form-label">Origin URL</label>
                <input type="url" name="origin_url" value="{{ old('origin_url', $customer->origin_url) }}" class="form-control" required>
                @error('origin_url')<div class="c-red-500 mT-5 fsz-sm">{{ $message }}</div>@enderror
            </div>

            <div class="mB-20">
                <label class="form-label">プラン</label>
                <select name="plan_id" class="form-select" required>
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}" @selected(old('plan_id', $customer->plan_id) == $plan->id)>{{ $plan->name }}</option>
                    @endforeach
                </select>
                @error('plan_id')<div class="c-red-500 mT-5 fsz-sm">{{ $message }}</div>@enderror
            </div>

            <div class="form-check mB-20">
                <input type="checkbox" name="active" value="1" class="form-check-input" id="activeCheck" @checked(old('active', $customer->active))>
                <label class="form-check-label" for="activeCheck">有効</label>
            </div>

            <div class="mT-20">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-check mR-5"></i> 更新
                </button>
                <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-outline-secondary mL-10">キャンセル</a>
            </div>
        </form>
    </div>
@endsection
