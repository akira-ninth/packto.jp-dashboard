<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Packto Console')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Hiragino Kaku Gothic ProN", Meiryo, sans-serif; background: #f1f5f9; min-height: 100vh; display: flex; flex-direction: column; }
        .auth-header { background: #1e293b; color: #fff; padding: 14px 24px; }
        .auth-header a { color: #60a5fa; text-decoration: none; font-weight: 700; font-size: 17px; }
        .auth-header a i { margin-right: 6px; }
        .auth-body { flex: 1; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .auth-card { width: 100%; max-width: 440px; }
        .card { border: none; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
    </style>
</head>
<body>
    <div class="auth-header">
        <a href="/"><i class="bi bi-box-seam"></i>Packto Console</a>
    </div>
    <div class="auth-body">
        <div class="auth-card">
            @yield('content')
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
