@extends('layouts.admin')

@section('title', '顧客一覧 | Packto Console')

@section('content')
    <div class="peers ai-c jc-sb fxw-nw mT-10 mB-30">
        <div class="peer">
            <h4 class="c-grey-900">顧客一覧</h4>
        </div>
        <div class="peer">
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus mR-5"></i> 新規顧客追加
            </a>
        </div>
    </div>

    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <table class="table">
            <thead>
                <tr>
                    <th>サブドメイン</th>
                    <th>表示名</th>
                    <th>プラン</th>
                    <th>状態</th>
                    <th class="text-end">先月 req</th>
                    <th class="text-end">先月 転送量</th>
                    <th class="text-end"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    @php
                        $usage = $usageMap[$customer->subdomain] ?? null;
                        $reqs = $usage ? (int) $usage['reqs'] : 0;
                        $outMb = $usage ? (float) $usage['output_bytes'] / 1024 / 1024 : 0;
                    @endphp
                    <tr>
                        <td><code class="c-blue-500">{{ $customer->subdomain }}.packto.jp</code></td>
                        <td>{{ $customer->display_name }}</td>
                        <td><span class="badge badge-plan-{{ $customer->plan->slug }}">{{ $customer->plan->name }}</span></td>
                        <td>
                            @if ($customer->active)
                                <span class="badge badge-active"><i class="fa fa-check-circle mR-5"></i>有効</span>
                            @else
                                <span class="badge badge-inactive"><i class="fa fa-times-circle mR-5"></i>停止</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if ($reqs > 0)
                                <span class="fw-600">{{ number_format($reqs) }}</span>
                            @else
                                <span class="c-grey-400">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if ($outMb > 0)
                                <span class="fw-600">{{ number_format($outMb, 1) }} <span class="c-grey-500 fsz-sm">MB</span></span>
                            @else
                                <span class="c-grey-400">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fa fa-eye mR-5"></i> 詳細
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="ta-c c-grey-600 pY-20">顧客がいません</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
