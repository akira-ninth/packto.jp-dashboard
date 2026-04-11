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
        <div class="pos-a centerXY" style="text-align: center;">
            <div class="{{ $isAdmin ? 'bgc-grey-900' : 'bgc-white' }} bdrs-50p pos-r" style="width: 120px; height: 120px; margin: 0 auto 1.5rem; {{ $isAdmin ? 'border: 2px solid #2d3139;' : '' }}">
                <img class="pos-a centerXY" src="/adminator/logo.svg" alt="" style="max-width: 60px; {{ $isAdmin ? 'filter: invert(1);' : '' }}">
            </div>
            <h2 style="color: {{ $isAdmin ? '#e2e8f0' : '#fff' }}; font-size: 1.75rem; font-weight: 700; margin: 0 0 .5rem; letter-spacing: .02em;">Packto</h2>
            <p style="color: {{ $isAdmin ? '#64748b' : 'rgba(255,255,255,.7)' }}; font-size: .875rem; margin: 0 0 1.5rem;">超高速Web配信サービス</p>
            @if(!$isAdmin)
                <a href="https://packto.jp/" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; color: rgba(255,255,255,.85); font-size: .8rem; font-weight: 600; text-decoration: none; border: 1px solid rgba(255,255,255,.25); border-radius: 60px; transition: background .2s, border-color .2s;" onmouseover="this.style.background='rgba(255,255,255,.1)';this.style.borderColor='rgba(255,255,255,.5)'" onmouseout="this.style.background='transparent';this.style.borderColor='rgba(255,255,255,.25)'">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    サービスサイトに戻る
                </a>
            @endif
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
