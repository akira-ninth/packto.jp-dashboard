@extends('layouts.admin')

@section('title', '管理ダッシュボード | Packto Console')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0 fw-bold">ダッシュボード</h1>
    </div>

    {{-- Stat Blocks --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <a class="block block-rounded block-link-shadow text-end" href="{{ route('admin.customers.index') }}">
                <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
                    <div class="d-none d-sm-block">
                        <i class="fa fa-users fa-2x opacity-25"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-semibold">{{ $customerCount }}</div>
                        <div class="fs-sm fw-semibold text-uppercase text-muted">顧客数</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
                    <div class="d-none d-sm-block">
                        <i class="fa fa-circle-check fa-2x text-success opacity-50"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-semibold">{{ $activeCount }}</div>
                        <div class="fs-sm fw-semibold text-uppercase text-muted">有効</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
                    <div class="d-none d-sm-block">
                        <i class="fa fa-star fa-2x opacity-25" style="color: #6d28d9;"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-semibold">{{ $planCounts->firstWhere('slug', 'pro')?->customers_count ?? 0 }}</div>
                        <div class="fs-sm fw-semibold text-uppercase text-muted">Pro プラン</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a class="block block-rounded block-link-shadow text-end" href="javascript:void(0)">
                <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
                    <div class="d-none d-sm-block">
                        <i class="fa fa-box fa-2x opacity-25"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-semibold">{{ $planCounts->firstWhere('slug', 'basic')?->customers_count ?? 0 }}</div>
                        <div class="fs-sm fw-semibold text-uppercase text-muted">Basic プラン</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Plan breakdown table --}}
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title"><i class="fa fa-chart-bar me-2 opacity-50"></i>プラン別</h3>
        </div>
        <div class="block-content p-0">
            <table class="table table-hover table-vcenter mb-0">
                <thead>
                    <tr><th>プラン</th><th class="text-end">顧客数</th></tr>
                </thead>
                <tbody>
                    @foreach ($planCounts as $plan)
                        <tr>
                            <td><span class="badge badge-plan-{{ $plan->slug }}">{{ $plan->name }}</span></td>
                            <td class="text-end">{{ $plan->customers_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
