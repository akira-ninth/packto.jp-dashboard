@extends('layouts.app')

@section('title', '顧客一覧 | Packto Console')

@section('nav')
    <nav>
        <a href="{{ route('admin.dashboard') }}">ダッシュボード</a>
        <a href="{{ route('admin.customers.index') }}">顧客一覧</a>
        <a href="{{ route('admin.masters.index') }}">マスター</a>
    </nav>
@endsection

@section('content')
    <h1>顧客一覧</h1>

    <p><a href="{{ route('admin.customers.create') }}" class="btn">+ 新規顧客追加</a></p>

    <div class="card" style="padding: 0;">
        <table>
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
                        <td><code>{{ $customer->subdomain }}.packto.jp</code></td>
                        <td>{{ $customer->display_name }}</td>
                        <td><a href="{{ $customer->origin_url }}" target="_blank" rel="noopener">{{ $customer->origin_url }}</a></td>
                        <td><span class="badge {{ $customer->plan->slug }}">{{ $customer->plan->name }}</span></td>
                        <td>
                            @if ($customer->active)
                                <span class="badge active">有効</span>
                            @else
                                <span class="badge inactive">停止</span>
                            @endif
                        </td>
                        <td><a href="{{ route('admin.customers.show', $customer) }}">詳細</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align: center; padding: 24px; color: #6b7280;">顧客がいません</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
