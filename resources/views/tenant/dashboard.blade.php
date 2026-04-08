@extends('layouts.app')

@section('title', 'ダッシュボード | Packto')

@section('nav')
    <nav>
        <a href="{{ route('tenant.dashboard') }}">ダッシュボード</a>
    </nav>
@endsection

@section('content')
    <h1>ダッシュボード</h1>

    @if ($customer)
        <div class="card">
            <h2>{{ $customer->display_name }}</h2>
            <table>
                <tr><th style="width: 180px;">配信ドメイン</th><td><code>{{ $customer->subdomain }}.packto.jp</code></td></tr>
                <tr><th>Origin</th><td>{{ $customer->origin_url }}</td></tr>
                <tr><th>プラン</th><td><span class="badge {{ $customer->plan->slug }}">{{ $customer->plan->name }}</span></td></tr>
                <tr><th>状態</th><td>
                    @if ($customer->active)
                        <span class="badge active">配信中</span>
                    @else
                        <span class="badge inactive">停止中</span>
                    @endif
                </td></tr>
            </table>
        </div>

        <div class="card">
            <h2>使用量 (Phase 12 で実装予定)</h2>
            <p style="color: #6b7280;">Cloudflare Analytics Engine 統合後にここへ表示します</p>
        </div>
    @else
        <div class="card">
            <p>顧客情報が紐付いていません。マスターアカウント (admin) にお問い合わせください。</p>
        </div>
    @endif
@endsection
