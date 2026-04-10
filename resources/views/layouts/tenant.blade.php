<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Packto')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 220px;
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-active: #2563eb;
            --header-height: 56px;
        }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Hiragino Kaku Gothic ProN", Meiryo, sans-serif; background: #f1f5f9; }
        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-width); background: var(--sidebar-bg);
            z-index: 1030; overflow-y: auto; transition: transform .2s;
        }
        .sidebar-brand {
            height: var(--header-height); display: flex; align-items: center;
            padding: 0 20px; font-size: 16px; font-weight: 700; color: #fff;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .sidebar-brand i { font-size: 20px; margin-right: 8px; color: #60a5fa; }
        .sidebar-nav { padding: 12px 0; }
        .sidebar-heading {
            padding: 10px 20px 6px; font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .05em; color: #475569;
        }
        .sidebar-link {
            display: flex; align-items: center; padding: 9px 20px;
            color: #94a3b8; text-decoration: none; font-size: 13.5px; transition: all .15s;
        }
        .sidebar-link:hover { background: var(--sidebar-hover); color: #fff; }
        .sidebar-link.active { background: var(--sidebar-active); color: #fff; }
        .sidebar-link i { width: 22px; font-size: 16px; margin-right: 10px; text-align: center; }
        .main-header {
            position: fixed; top: 0; left: var(--sidebar-width); right: 0;
            height: var(--header-height); background: #fff;
            border-bottom: 1px solid #e2e8f0; z-index: 1020;
            display: flex; align-items: center; padding: 0 24px;
        }
        .main-header .spacer { flex: 1; }
        .main-header .user-info { font-size: 13px; color: #64748b; }
        .main-header .user-info strong { color: #1e293b; }
        .main-content { margin-left: var(--sidebar-width); margin-top: var(--header-height); padding: 24px; }
        .stat-tile { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,.04); transition: box-shadow .15s; }
        .stat-tile:hover { box-shadow: 0 4px 12px rgba(0,0,0,.08); }
        .stat-tile .stat-icon { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #fff; }
        .stat-tile .stat-value { font-size: 28px; font-weight: 700; color: #1e293b; }
        .stat-tile .stat-label { font-size: 12px; color: #94a3b8; font-weight: 500; text-transform: uppercase; letter-spacing: .03em; }
        .card { border: none; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
        .card-header { background: #fff; border-bottom: 1px solid #f1f5f9; font-weight: 600; font-size: 15px; }
        .table th { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .03em; color: #64748b; background: #f8fafc; }
        .table td { vertical-align: middle; font-size: 13.5px; }
        .badge-plan-basic { background: #e2e8f0; color: #475569; }
        .badge-plan-pro { background: #ede9fe; color: #6d28d9; }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }
        .sidebar-toggle { display: none; }
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-header { left: 0; }
            .main-content { margin-left: 0; }
            .sidebar-toggle { display: inline-flex; }
            .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 1025; }
            .sidebar-overlay.show { display: block; }
        }
    </style>
    @yield('head')
</head>
<body>
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-box-seam"></i> Packto
        </div>
        <div class="sidebar-nav">
            <div class="sidebar-heading">メイン</div>
            <a href="{{ route('tenant.dashboard') }}" class="sidebar-link @if(request()->routeIs('tenant.dashboard')) active @endif">
                <i class="bi bi-speedometer2"></i> ダッシュボード
            </a>

            <div class="sidebar-heading">アカウント</div>
            <a href="{{ route('account.edit') }}" class="sidebar-link @if(request()->routeIs('account.*')) active @endif">
                <i class="bi bi-person-gear"></i> アカウント設定
            </a>
            <a href="{{ route('two-factor.show') }}" class="sidebar-link @if(request()->routeIs('two-factor.*')) active @endif">
                <i class="bi bi-shield-check"></i> 2FA 設定
            </a>
        </div>
    </nav>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <header class="main-header">
        <button class="btn btn-sm btn-light sidebar-toggle me-3" onclick="toggleSidebar()">
            <i class="bi bi-list fs-5"></i>
        </button>
        <div class="spacer"></div>
        @auth
            <div class="user-info me-3">
                <strong>{{ auth()->user()->name }}</strong>
            </div>
            <form method="POST" action="{{ url('/logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        @endauth
    </header>

    <main class="main-content">
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }
    </script>
    @yield('scripts')
</body>
</html>
