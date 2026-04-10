@extends('layouts.admin')

@section('title', '顧客一覧 | Packto Console')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0 fw-bold">顧客一覧</h1>
        <a href="{{ route('admin.customers.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> 新規顧客追加
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>サブドメイン</th>
                        <th>表示名</th>
                        <th>Origin</th>
                        <th>プラン</th>
                        <th>状態</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td><code class="text-primary">{{ $customer->subdomain }}.packto.jp</code></td>
                            <td>{{ $customer->display_name }}</td>
                            <td><a href="{{ $customer->origin_url }}" target="_blank" rel="noopener" class="text-muted small">{{ $customer->origin_url }}</a></td>
                            <td><span class="badge badge-plan-{{ $customer->plan->slug }}">{{ $customer->plan->name }}</span></td>
                            <td>
                                @if ($customer->active)
                                    <span class="badge badge-active"><i class="bi bi-check-circle me-1"></i>有効</span>
                                @else
                                    <span class="badge badge-inactive"><i class="bi bi-x-circle me-1"></i>停止</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> 詳細
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">顧客がいません</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
