@php
    $isAdmin = str_contains(request()->getHost(), 'admin.');
@endphp
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
        /* === Auth shared === */
        .auth-left {
            background: {{ $isAdmin ? '#1a1d21' : 'linear-gradient(135deg, #6366f1, #4f46e5)' }};
        }
        .auth-right {
            background: {{ $isAdmin ? '#111318' : '#fff' }};
        }

        @if ($isAdmin)
        /* === Master dark login === */
        .auth-right h4 { color: #e2e8f0 !important; }
        .auth-right .form-label { color: #94a3b8 !important; }
        .auth-right .form-control {
            background: #1e2028 !important;
            border-color: #2d3139 !important;
            color: #e2e8f0 !important;
        }
        .auth-right .form-control:focus {
            background: #252830 !important;
            border-color: #6366f1 !important;
            color: #fff !important;
            box-shadow: 0 0 0 .2rem rgba(99,102,241,.25) !important;
        }
        .auth-right .form-check-label { color: #94a3b8 !important; }
        .auth-right a { color: #818cf8 !important; }
        .auth-right .alert-success { background: #1a2e1a; border-color: #2d4a2d; color: #86efac; }
        .auth-right p { color: #94a3b8; }
        @endif
    </style>
</head>
<body class="app">
<div class="peers ai-s fxw-nw h-100vh">
    {{-- Left panel --}}
    <div class="d-n@sm- peer peer-greed h-100 pos-r auth-left">
        <div class="pos-a centerXY">
            <div class="{{ $isAdmin ? 'bgc-grey-900' : 'bgc-white' }} bdrs-50p pos-r" style="width: 120px; height: 120px; {{ $isAdmin ? 'border: 2px solid #2d3139;' : '' }}">
                <img class="pos-a centerXY" src="/adminator/logo.svg" alt="" style="max-width: 60px; {{ $isAdmin ? 'filter: invert(1);' : '' }}">
            </div>
        </div>
    </div>

    {{-- Right panel --}}
    <div class="col-12 col-md-4 peer pX-40 pY-80 h-100 scrollable pos-r auth-right" style="min-width: 320px;">
        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@yield('scripts')
</body>
</html>
