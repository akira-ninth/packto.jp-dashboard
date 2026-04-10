@extends('layouts.admin')

@section('title', '顧客編集 | Packto Console')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0">顧客編集 — {{ $customer->subdomain }}</h1>
        <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> 詳細に戻る
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
                @csrf
                @method('PATCH')

                <div class="mb-3">
                    <label class="form-label">サブドメイン (変更不可)</label>
                    <input type="text" value="{{ $customer->subdomain }}.packto.jp" class="form-control" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">表示名</label>
                    <input type="text" name="display_name" value="{{ old('display_name', $customer->display_name) }}" class="form-control" required>
                    @error('display_name')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Origin URL</label>
                    <input type="url" name="origin_url" value="{{ old('origin_url', $customer->origin_url) }}" class="form-control" required>
                    @error('origin_url')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">プラン</label>
                    <select name="plan_id" class="form-select" required>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" @selected(old('plan_id', $customer->plan_id) == $plan->id)>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                    @error('plan_id')<div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>@enderror
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="active" value="1" class="form-check-input" id="activeCheck" @checked(old('active', $customer->active))>
                    <label class="form-check-label" for="activeCheck">有効</label>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> 更新
                    </button>
                    <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-outline-secondary ms-2">キャンセル</a>
                </div>
            </form>
        </div>
    </div>
@endsection
