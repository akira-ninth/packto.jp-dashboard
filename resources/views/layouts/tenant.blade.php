<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Packto')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        /* ===== Codebase-style Tenant Theme ===== */

        :root {
            --cb-sidebar-width: 230px;
            --cb-sidebar-bg: #1e293b;
            --cb-sidebar-text: #b0bec5;
            --cb-sidebar-active: #3b82f6;
            --cb-header-height: 60px;
            --cb-body-bg: #f0f2f5;
            --cb-primary: #3b82f6;
            --cb-text: #3e4a59;
            --cb-text-muted: #6c757d;
            --cb-block-shadow: 0 2px 6px rgba(0,0,0,.04);
        }
        body {
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", "Hiragino Kaku Gothic ProN", Meiryo, sans-serif;
            background: var(--cb-body-bg);
            color: var(--cb-text);
            margin: 0;
            overflow-x: hidden;
        }

        #page-container { display: flex; min-height: 100vh; }

        /* Sidebar */
        #sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--cb-sidebar-width);
            background: var(--cb-sidebar-bg);
            z-index: 1030; overflow-y: auto; overflow-x: hidden;
            transition: transform .28s ease;
        }
        .sidebar-content { display: flex; flex-direction: column; min-height: 100%; }
        .content-header {
            display: flex; align-items: center;
            padding: 0 1rem; min-height: var(--cb-header-height);
        }
        #sidebar .content-header {
            justify-content: center;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }
        .content-side { padding: 1rem 0; }
        .content-side-full { flex: 1; }
        .link-fx { text-decoration: none; }
        .link-fx:hover { text-decoration: none; }
        .text-dual { color: #fff; }

        /* nav-main */
        .nav-main { list-style: none; padding: 0; margin: 0; }
        .nav-main-heading {
            padding: 1.25rem 1.25rem .375rem;
            font-size: .6875rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: .06em; color: #64748b;
        }
        .nav-main-item { margin: 0; }
        .nav-main-link {
            display: flex; align-items: center;
            padding: .5625rem 1.25rem;
            color: var(--cb-sidebar-text); text-decoration: none;
            font-size: .875rem; font-weight: 500;
            transition: background .15s, color .15s;
            border-left: 3px solid transparent;
        }
        .nav-main-link:hover { background: rgba(255,255,255,.05); color: #fff; }
        .nav-main-link.active {
            background: rgba(59,130,246,.15); color: #fff;
            border-left-color: var(--cb-sidebar-active);
        }
        .nav-main-link-icon {
            width: 1.625rem; font-size: 1rem; margin-right: .625rem;
            text-align: center; opacity: .7; flex-shrink: 0;
        }
        .nav-main-link.active .nav-main-link-icon { opacity: 1; color: var(--cb-sidebar-active); }
        .nav-main-link-name { flex: 1; }

        /* Header */
        #page-header {
            position: fixed; top: 0; left: var(--cb-sidebar-width); right: 0;
            height: var(--cb-header-height); background: #fff;
            border-bottom: 1px solid #e5e7eb; z-index: 1020;
            display: flex; align-items: center;
        }
        #page-header .content-header {
            flex: 1; padding: 0 1.25rem; justify-content: space-between;
        }

        /* Main */
        #main-container {
            margin-left: var(--cb-sidebar-width);
            margin-top: var(--cb-header-height);
            min-height: calc(100vh - var(--cb-header-height));
        }
        #main-container > .content { padding: 1.5rem; max-width: 1280px; }

        /* Block */
        .block { background: #fff; margin-bottom: 1.5rem; box-shadow: var(--cb-block-shadow); }
        .block-rounded { border-radius: .5rem; }
        .block-link-shadow { transition: box-shadow .15s ease-out; text-decoration: none; color: inherit; }
        .block-link-shadow:hover { box-shadow: 0 6px 18px rgba(0,0,0,.1); color: inherit; }
        .block-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: .875rem 1.25rem; border-bottom: 1px solid #f0f2f5;
        }
        .block-header-default { background: transparent; }
        .block-title { font-size: .9375rem; font-weight: 600; margin: 0; }
        .block-content { padding: 1.25rem; }
        .block-content-full { padding: 1.25rem; }
        .block-options { display: flex; align-items: center; gap: .5rem; }

        /* Buttons alt */
        .btn-alt-primary { color: #3b82f6; background-color: rgba(59,130,246,.12); border-color: transparent; }
        .btn-alt-primary:hover, .btn-alt-primary:focus { color: #fff; background-color: #3b82f6; border-color: #3b82f6; }
        .btn-alt-secondary { color: #475569; background-color: #e2e8f0; border-color: #e2e8f0; }
        .btn-alt-secondary:hover, .btn-alt-secondary:focus { color: #fff; background-color: #64748b; border-color: #64748b; }
        .btn-alt-danger { color: #e53e3e; background-color: rgba(229,62,62,.12); border-color: transparent; }
        .btn-alt-danger:hover, .btn-alt-danger:focus { color: #fff; background-color: #e53e3e; border-color: #e53e3e; }
        .btn-alt-success { color: #059669; background-color: rgba(5,150,105,.12); border-color: transparent; }
        .btn-alt-success:hover, .btn-alt-success:focus { color: #fff; background-color: #059669; border-color: #059669; }

        .fs-sm { font-size: .875rem; }

        /* Table */
        .table-vcenter td, .table-vcenter th { vertical-align: middle; }
        .table thead th {
            font-size: .75rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: .04em;
            color: #64748b; background: #f8fafc; border-bottom-width: 1px;
        }
        .table td { font-size: .8125rem; }

        /* Badges */
        .badge-plan-basic { background: #e2e8f0; color: #475569; }
        .badge-plan-pro { background: #ede9fe; color: #6d28d9; }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }

        /* Overlay */
        .page-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.4); z-index: 1025; cursor: pointer;
        }
        .page-overlay.show { display: block; }

        /* Mobile */
        @media (max-width: 991.98px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.sidebar-o { transform: translateX(0); }
            #page-header { left: 0; }
            #main-container { margin-left: 0; }
            .sidebar-toggle-btn { display: inline-flex !important; }
        }
        @media (min-width: 992px) {
            .sidebar-toggle-btn { display: inline-flex !important; }
        }
    </style>
    @yield('head')
</head>
<body>
    <div id="page-container">

        {{-- Sidebar --}}
        <nav id="sidebar">
            <div class="sidebar-content">
                <div class="content-header justify-content-lg-center">
                    <a class="link-fx fw-bold" href="{{ route('tenant.dashboard') }}">
                        <i class="fa fa-box text-primary"></i>
                        <span class="fs-4 text-dual"> Pack</span><span class="fs-4 text-primary">to</span>
                    </a>
                </div>
                <div class="content-side content-side-full">
                    <ul class="nav-main">
                        <li class="nav-main-heading">メイン</li>
                        <li class="nav-main-item">
                            <a class="nav-main-link @if(request()->routeIs('tenant.dashboard')) active @endif" href="{{ route('tenant.dashboard') }}">
                                <i class="nav-main-link-icon fa fa-gauge-high"></i>
                                <span class="nav-main-link-name">ダッシュボード</span>
                            </a>
                        </li>

                        <li class="nav-main-heading">アカウント</li>
                        <li class="nav-main-item">
                            <a class="nav-main-link @if(request()->routeIs('account.*')) active @endif" href="{{ route('account.edit') }}">
                                <i class="nav-main-link-icon fa fa-user-gear"></i>
                                <span class="nav-main-link-name">アカウント設定</span>
                            </a>
                        </li>
                        <li class="nav-main-item">
                            <a class="nav-main-link @if(request()->routeIs('two-factor.*')) active @endif" href="{{ route('two-factor.show') }}">
                                <i class="nav-main-link-icon fa fa-shield-check"></i>
                                <span class="nav-main-link-name">2FA 設定</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="page-overlay" id="pageOverlay" onclick="toggleSidebar()"></div>

        {{-- Header --}}
        <header id="page-header">
            <div class="content-header">
                <div class="d-flex align-items-center">
                    <button class="btn btn-sm btn-alt-secondary sidebar-toggle-btn" type="button" onclick="toggleSidebar()">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>
                </div>
                <div class="d-flex align-items-center">
                    @auth
                        <span class="fs-sm text-muted me-2">
                            <i class="fa fa-user-circle me-1"></i>
                            <strong class="text-dark">{{ auth()->user()->name }}</strong>
                        </span>
                        <form method="POST" action="{{ url('/logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-alt-secondary">
                                <i class="fa fa-right-from-bracket"></i>
                            </button>
                        </form>
                    @endauth
                </div>
            </div>
        </header>

        {{-- Main Content --}}
        <main id="main-container">
            <div class="content">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('sidebar-o');
            document.getElementById('pageOverlay').classList.toggle('show');
        }
    </script>
    @yield('scripts')
</body>
</html>
