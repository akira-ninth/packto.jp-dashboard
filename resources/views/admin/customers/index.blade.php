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
                    <th></th>
                    <th>サイト名</th>
                    <th>ワーカードメイン</th>
                    <th>プラン</th>
                    <th>状態</th>
                    <th class="text-end">先月 req</th>
                    <th class="text-end">先月 転送量</th>
                    <th>最終ログイン</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    @php
                        $usage = $usageMap[$customer->subdomain] ?? null;
                        $reqs = $usage ? (int) $usage['reqs'] : 0;
                        $outMb = $usage ? (float) $usage['output_bytes'] / 1024 / 1024 : 0;
                        $lastLogin = $customer->users->max('last_login_at');
                    @endphp
                    <tr>
                        <td style="white-space: nowrap;">
                            <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm" style="background:#e9ecef;color:#495057;border:1px solid #ced4da;">
                                <i class="fa fa-folder-open mR-3"></i>詳細
                            </a>
                            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm mL-5" style="background:#e9ecef;color:#495057;border:1px solid #ced4da;">
                                <i class="fa fa-pencil mR-3"></i>編集
                            </a>
                        </td>
                        <td><a href="{{ route('admin.customers.show', $customer) }}" class="c-grey-900 fw-600 td-n">{{ $customer->display_name }}</a></td>
                        <td><code class="c-blue-500">{{ $customer->subdomain }}.packto.jp</code></td>
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
                        <td>
                            @if ($lastLogin)
                                <span class="fsz-sm c-grey-600">{{ $lastLogin->format('m/d H:i') }}</span>
                            @else
                                <span class="c-grey-400 fsz-sm">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="ta-c c-grey-600 pY-20">顧客がいません</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
