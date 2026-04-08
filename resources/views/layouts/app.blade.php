<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Hiragino Kaku Gothic ProN", Meiryo, sans-serif; margin: 0; background: #f7f7f9; color: #1f2937; }
        header { background: #1f2937; color: #fff; padding: 12px 24px; display: flex; align-items: center; gap: 16px; }
        header .brand { font-weight: 700; font-size: 18px; }
        header nav a { color: #cbd5e1; margin-right: 16px; text-decoration: none; }
        header nav a:hover { color: #fff; }
        header .spacer { flex: 1; }
        header .user { font-size: 13px; color: #cbd5e1; }
        main { max-width: 1100px; margin: 24px auto; padding: 0 24px; }
        h1 { font-size: 22px; margin: 0 0 16px; }
        h2 { font-size: 18px; margin: 24px 0 12px; }
        .card { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px 12px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
        th { background: #f3f4f6; font-weight: 600; }
        a.btn { display: inline-block; padding: 8px 14px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 6px; font-size: 14px; }
        a.btn.secondary { background: #6b7280; }
        a.btn.danger { background: #dc2626; }
        .status { padding: 12px; background: #d1fae5; color: #065f46; border-radius: 6px; margin-bottom: 16px; }
        form label { display: block; margin: 12px 0 4px; font-size: 14px; font-weight: 600; }
        form input[type=text], form input[type=email], form input[type=password], form input[type=url], form select { width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; }
        form .errors { color: #dc2626; font-size: 13px; margin-top: 4px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge.basic { background: #e5e7eb; color: #374151; }
        .badge.pro { background: #ddd6fe; color: #5b21b6; }
        .badge.active { background: #d1fae5; color: #065f46; }
        .badge.inactive { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <header>
        <div class="brand">{{ config('app.name') }}</div>
        @yield('nav')
        <div class="spacer"></div>
        @auth
            <div class="user">{{ auth()->user()->email }} ({{ auth()->user()->role }})</div>
            <a href="{{ route('account.edit') }}" style="color: #cbd5e1; text-decoration: none; font-size: 12px; padding: 4px 12px; border: 1px solid #4b5563; border-radius: 4px;">アカウント</a>
            <form method="POST" action="{{ url('/logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" style="background: transparent; border: 1px solid #4b5563; color: #cbd5e1; padding: 4px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">ログアウト</button>
            </form>
        @endauth
    </header>
    <main>
        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>
