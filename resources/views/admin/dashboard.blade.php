@extends('layouts.app')

@section('title', '管理ダッシュボード | Packto Console')

@section('nav')
    <nav>
        <a href="{{ route('admin.dashboard') }}">ダッシュボード</a>
        <a href="{{ route('admin.customers.index') }}">顧客一覧</a>
    </nav>
@endsection

@section('content')
    <h1>管理ダッシュボード</h1>

    <div class="card">
        <h2>顧客数</h2>
        <p style="font-size: 36px; margin: 0;">{{ $customerCount }}<span style="font-size: 14px; color: #6b7280;"> (active: {{ $activeCount }})</span></p>
    </div>

    <div class="card">
        <h2>プラン別</h2>
        <table>
            <thead><tr><th>プラン</th><th>顧客数</th></tr></thead>
            <tbody>
                @foreach ($planCounts as $plan)
                    <tr>
                        <td><span class="badge {{ $plan->slug }}">{{ $plan->name }}</span></td>
                        <td>{{ $plan->customers_count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
