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
        /* ===== Codebase-style Auth (Login) Theme ===== */
        body {
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", "Hiragino Kaku Gothic ProN", Meiryo, sans-serif;
            margin: 0;
        }

        /* bg-gd-dusk */
        .bg-gd-dusk {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            min-height: 100vh;
        }

        /* hero-static */
        .hero-static {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* bg-body-extra-light */
        .bg-body-extra-light {
            background-color: #f8f9fa;
        }

        /* link-fx */
        .link-fx {
            text-decoration: none;
            position: relative;
        }
        .link-fx:hover {
            text-decoration: none;
        }

        /* text-dual (brand text on light bg) */
        .text-dual {
            color: #3e4a59;
        }

        /* Buttons alt */
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
            background-color: rgba(108,117,125,.12);
            border-color: transparent;
        }
        .btn-alt-secondary:hover, .btn-alt-secondary:focus {
            color: #fff;
            background-color: #6c757d;
            border-color: #6c757d;
        }

        /* fs-sm */
        .fs-sm {
            font-size: .875rem;
        }
    </style>
</head>
<body>
    <div class="bg-gd-dusk">
        <div class="hero-static content content-full bg-body-extra-light">
            <div class="py-4 px-1 text-center mb-4">
                <a class="link-fx fw-bold" href="/">
                    <i class="fa fa-box text-primary"></i>
                    <span class="fs-2 text-dual"> Pack</span><span class="fs-2 text-primary">to</span>
                </a>
            </div>
            @yield('content')
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
