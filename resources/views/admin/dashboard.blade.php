@extends('layouts.admin')

@section('title', '管理ダッシュボード | Packto Console')

@section('content')
    <h4 class="c-grey-900 mT-10 mB-30">ダッシュボード</h4>

    {{-- Stat Cards --}}
    <div class="row gap-20 mB-20">
        <div class="col-md-3">
            <div class="layers bd bgc-white p-20">
                <div class="layer w-100 mB-10">
                    <h6 class="lh-1">顧客数</h6>
                </div>
                <div class="layer w-100">
                    <div class="peers ai-sb fxw-nw">
                        <div class="peer peer-greed">
                            <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-blue-50 c-blue-500">
                                <i class="ti-user"></i>
                            </span>
                        </div>
                        <div class="peer">
                            <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15">
                                <span class="c-grey-900 fsz-lg">{{ $customerCount }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="layers bd bgc-white p-20">
                <div class="layer w-100 mB-10">
                    <h6 class="lh-1">有効</h6>
                </div>
                <div class="layer w-100">
                    <div class="peers ai-sb fxw-nw">
                        <div class="peer peer-greed">
                            <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-green-50 c-green-500">
                                <i class="ti-check"></i>
                            </span>
                        </div>
                        <div class="peer">
                            <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15">
                                <span class="c-grey-900 fsz-lg">{{ $activeCount }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="layers bd bgc-white p-20">
                <div class="layer w-100 mB-10">
                    <h6 class="lh-1">Pro プラン</h6>
                </div>
                <div class="layer w-100">
                    <div class="peers ai-sb fxw-nw">
                        <div class="peer peer-greed">
                            <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-purple-50 c-purple-500">
                                <i class="ti-star"></i>
                            </span>
                        </div>
                        <div class="peer">
                            <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15">
                                <span class="c-grey-900 fsz-lg">{{ $planCounts->firstWhere('slug', 'pro')?->customers_count ?? 0 }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="layers bd bgc-white p-20">
                <div class="layer w-100 mB-10">
                    <h6 class="lh-1">Basic プラン</h6>
                </div>
                <div class="layer w-100">
                    <div class="peers ai-sb fxw-nw">
                        <div class="peer peer-greed">
                            <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-grey-100 c-grey-600">
                                <i class="ti-package"></i>
                            </span>
                        </div>
                        <div class="peer">
                            <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15">
                                <span class="c-grey-900 fsz-lg">{{ $planCounts->firstWhere('slug', 'basic')?->customers_count ?? 0 }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Plan breakdown table --}}
    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <h4 class="c-grey-900 mB-20">プラン別</h4>
        <table class="table">
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
@endsection
