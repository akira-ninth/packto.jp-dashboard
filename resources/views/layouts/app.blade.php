<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Packto Console')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", "Hiragino Kaku Gothic ProN", Meiryo, sans-serif;
            margin: 0;
            background: #f0f2f5;
        }

        /* ===== Auth Page Layout ===== */

        /* Top gradient banner */
        .auth-banner {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            padding: 3rem 1rem 6rem;
            text-align: center;
        }
        .auth-banner .brand {
            font-size: 1.75rem;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
        }
        .auth-banner .brand i {
            margin-right: .375rem;
            opacity: .85;
        }
        .auth-banner h1 {
            color: rgba(255,255,255,.95);
            font-size: 1.375rem;
            font-weight: 600;
            margin: 1.5rem 0 .375rem;
        }
        .auth-banner h2 {
            color: rgba(255,255,255,.65);
            font-size: .9375rem;
            font-weight: 400;
            margin: 0;
        }

        /* Form card overlapping the banner */
        .auth-form-wrapper {
            max-width: 420px;
            margin: -3.5rem auto 2rem;
            padding: 0 1rem;
        }
        .auth-card {
            background: #fff;
            border-radius: .625rem;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            padding: 2rem 1.75rem;
        }

        /* Buttons */
        .btn-alt-primary {
            color: #fff;
            background-color: #3b82f6;
            border-color: #3b82f6;
        }
        .btn-alt-primary:hover, .btn-alt-primary:focus {
            color: #fff;
            background-color: #2563eb;
            border-color: #2563eb;
            box-shadow: 0 4px 12px rgba(59,130,246,.35);
        }
        .btn-alt-secondary {
            color: #6c757d;
            background-color: #e9ecef;
            border-color: #e9ecef;
        }
        .btn-alt-secondary:hover, .btn-alt-secondary:focus {
            color: #fff;
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .fs-sm { font-size: .875rem; }
    </style>
</head>
<body>
    {{-- Top gradient banner with brand --}}
    <div class="auth-banner">
        <a class="brand" href="/">
            <i class="fa fa-box"></i> Packto
        </a>
        @yield('auth-heading')
    </div>

    {{-- Form card --}}
    <div class="auth-form-wrapper">
        <div class="auth-card">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
