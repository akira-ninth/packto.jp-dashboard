<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Packto Console')</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="/adminator/style.css" rel="stylesheet">
    <style>
        .badge-plan-basic { background: #e2e8f0; color: #475569; }
        .badge-plan-pro { background: #ede9fe; color: #6d28d9; }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }

        /* === Master 専用: ダークサイドバー ===
         * Adminator は CSS 変数 + color-mix + :root prefix で !important を多用するため
         * sidebar スコープで変数を上書きし、さらに :root 付き selector で個別 override する。
         */
        .sidebar {
            --c-bkg-body: #1a1d21;
            --c-bkg-card: #1a1d21;
            --c-bkg-sidebar: #1a1d21;
            --c-bkg-hover: #2d3139;
            --c-text-base: #cbd5e1;
            --c-text-muted: #64748b;
            --c-border: #2d3139;
            --c-border-light: #252830;
            --c-icon: #94a3b8;
            --c-icon-hover: #fff;
            --c-icon-active: #a5b4fc;
            --c-primary: #818cf8;
            --c-primary-light: #a5b4fc;
            background: #1a1d21 !important;
        }
        .sidebar .sidebar-inner { background: #1a1d21 !important; }
        .sidebar .sidebar-logo { border-bottom: 1px solid rgba(255,255,255,.08) !important; }
        .sidebar .logo-text { color: #fff !important; }

        /* 通常リンク */
        .sidebar .sidebar-menu li a,
        .sidebar .sidebar-menu li a .title { color: #a0aec0 !important; }

        /* hover: ダーク背景で白テキスト */
        .sidebar .sidebar-menu li a:hover,
        .sidebar .sidebar-menu li a:focus,
        :root .sidebar .sidebar-menu li a:hover,
        :root .sidebar .sidebar-menu li a:focus { background-color: #2d3139 !important; color: #fff !important; }
        .sidebar .sidebar-menu li a:hover .title,
        .sidebar .sidebar-menu li a:focus .title { color: #fff !important; }
        .sidebar .sidebar-menu li a:hover .icon-holder,
        .sidebar .sidebar-menu li a:focus .icon-holder { color: #fff !important; }

        /* active: Adminator の :root .sidebar selector を完全 override */
        :root .sidebar .sidebar-menu li.actived > a,
        .sidebar .sidebar-menu li.actived > a {
            background-color: rgba(99,102,241,.2) !important;
            color: #fff !important;
            border-left: 3px solid #818cf8 !important;
            margin-left: -3px;
        }
        :root .sidebar .sidebar-menu li.actived > a .title { color: #fff !important; font-weight: 600; }
        :root .sidebar .sidebar-menu li.actived > a .icon-holder,
        .sidebar .sidebar-menu li.actived > a .icon-holder {
            color: #a5b4fc !important;
            background-color: rgba(99,102,241,.15) !important;
        }
        .sidebar .nav-item.actived::before { background: #818cf8 !important; }

        /* dropdown */
        .sidebar .dropdown-menu { background: #2d3139 !important; border-color: #3d4249 !important; }
        .sidebar .dropdown-menu a { color: #cbd5e1 !important; }
        .sidebar .dropdown-menu a:hover { background: #3d4249 !important; color: #fff !important; }
        :root .sidebar .sidebar-menu > li.dropdown ul.dropdown-menu > li > a:hover,
        :root .sidebar .sidebar-menu > li.dropdown ul.dropdown-menu > li.actived a {
            background-color: #3d4249 !important;
            color: #fff !important;
        }
    </style>
    @yield('head')
</head>
<body class="app">
<div>
    {{-- Sidebar --}}
    <div class="sidebar">
        <div class="sidebar-inner">
            <div class="sidebar-logo">
                <div class="peers ai-c fxw-nw">
                    <div class="peer peer-greed">
                        <a class="sidebar-link td-n" href="{{ route('admin.dashboard') }}">
                            <div class="peers ai-c fxw-nw">
                                <div class="peer">
                                    <div class="logo">
                                        <img src="/adminator/logo.svg" alt="" style="width: 30px; height: 30px;">
                                    </div>
                                </div>
                                <div class="peer peer-greed">
                                    <h5 class="lh-1 mB-0 logo-text">Packto</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="peer">
                        <div class="mobile-toggle sidebar-toggle">
                            <a href="" class="td-n"><i class="ti-arrow-circle-left"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <ul class="sidebar-menu scrollable pos-r">
                <li class="nav-item mT-30 @if(request()->routeIs('admin.dashboard')) actived @endif">
                    <a class="sidebar-link" href="{{ route('admin.dashboard') }}">
                        <span class="icon-holder"><i class="c-blue-500 ti-home"></i></span>
                        <span class="title">ダッシュボード</span>
                    </a>
                </li>
                <li class="nav-item @if(request()->routeIs('admin.customers.index') || request()->routeIs('admin.customers.show') || request()->routeIs('admin.customers.edit')) actived @endif">
                    <a class="sidebar-link" href="{{ route('admin.customers.index') }}">
                        <span class="icon-holder"><i class="c-indigo-500 ti-user"></i></span>
                        <span class="title">顧客一覧</span>
                    </a>
                </li>
                <li class="nav-item @if(request()->routeIs('admin.customers.create')) actived @endif">
                    <a class="sidebar-link" href="{{ route('admin.customers.create') }}">
                        <span class="icon-holder"><i class="c-green-500 ti-plus"></i></span>
                        <span class="title">顧客追加</span>
                    </a>
                </li>
                <li class="nav-item @if(request()->routeIs('admin.masters.*')) actived @endif">
                    <a class="sidebar-link" href="{{ route('admin.masters.index') }}">
                        <span class="icon-holder"><i class="c-deep-purple-500 ti-shield"></i></span>
                        <span class="title">マスター</span>
                    </a>
                </li>
                <li class="nav-item @if(request()->routeIs('admin.audit-logs.*')) actived @endif">
                    <a class="sidebar-link" href="{{ route('admin.audit-logs.index') }}">
                        <span class="icon-holder"><i class="c-orange-500 ti-time"></i></span>
                        <span class="title">監査ログ</span>
                    </a>
                </li>
                <li class="nav-item @if(request()->routeIs('account.*')) actived @endif">
                    <a class="sidebar-link" href="{{ route('account.edit') }}">
                        <span class="icon-holder"><i class="c-grey-500 ti-settings"></i></span>
                        <span class="title">アカウント設定</span>
                    </a>
                </li>
                <li class="nav-item @if(request()->routeIs('two-factor.*')) actived @endif">
                    <a class="sidebar-link" href="{{ route('two-factor.show') }}">
                        <span class="icon-holder"><i class="c-red-500 ti-lock"></i></span>
                        <span class="title">2FA 設定</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- Page Container --}}
    <div class="page-container">
        {{-- Header --}}
        <div class="header navbar">
            <div class="header-container">
                <ul class="nav-left">
                    <li>
                        <a id="sidebar-toggle" class="sidebar-toggle" href="javascript:void(0);">
                            <i class="ti-menu"></i>
                        </a>
                    </li>
                </ul>
                <ul class="nav-right">
                    @auth
                    <li class="dropdown">
                        <a href="" class="dropdown-toggle no-after peers fxw-nw ai-c lh-1" data-bs-toggle="dropdown">
                            <div class="peer mR-10">
                                <i class="ti-user fs-5"></i>
                            </div>
                            <div class="peer">
                                <span class="fsz-sm c-grey-900">{{ auth()->user()->name }}</span>
                            </div>
                        </a>
                        <ul class="dropdown-menu fsz-sm">
                            <li>
                                <a href="{{ route('account.edit') }}" class="d-b td-n pY-5 bgcH-grey-100 c-grey-700">
                                    <i class="ti-settings mR-10"></i> <span>設定</span>
                                </a>
                            </li>
                            <li role="separator" class="divider"></li>
                            <li>
                                <form action="{{ url('/logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="d-b td-n pY-5 bgcH-grey-100 c-grey-700 w-100 text-start border-0 bg-transparent">
                                        <i class="ti-power-off mR-10"></i> <span>ログアウト</span>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @endauth
                </ul>
            </div>
        </div>

        {{-- Main Content --}}
        <main class="main-content bgc-grey-100">
            <div id="mainContent">
                <div class="container-fluid">
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show mT-20" role="alert">
                            {{ session('status') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @yield('content')
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@yield('scripts')
</body>
</html>
