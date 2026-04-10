@extends('layouts.admin')

@section('title', '管理ダッシュボード | Packto Console')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0 fw-bold">ダッシュボード</h1>
    </div>

    {{-- Stat Tiles --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="stat-tile">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary me-3"><i class="bi bi-people"></i></div>
                    <div>
                        <div class="stat-value">{{ $customerCount }}</div>
                        <div class="stat-label">顧客数</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-tile">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success me-3"><i class="bi bi-check-circle"></i></div>
                    <div>
                        <div class="stat-value">{{ $activeCount }}</div>
                        <div class="stat-label">有効</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-tile">
                <div class="d-flex align-items-center">
                    <div class="stat-icon" style="background:#6d28d9;" class="me-3"><i class="bi bi-stars"></i></div>
                    <div>
                        <div class="stat-value">{{ $planCounts->firstWhere('slug', 'pro')?->customers_count ?? 0 }}</div>
                        <div class="stat-label">Pro プラン</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-tile">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-secondary me-3"><i class="bi bi-box"></i></div>
                    <div>
                        <div class="stat-value">{{ $planCounts->firstWhere('slug', 'basic')?->customers_count ?? 0 }}</div>
                        <div class="stat-label">Basic プラン</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Plan breakdown table --}}
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <i class="bi bi-bar-chart me-2"></i> プラン別
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
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
