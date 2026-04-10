<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Packto Console')</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="/adminator/style.css" rel="stylesheet">
</head>
<body class="app">
<div class="peers ai-s fxw-nw h-100vh">
    {{-- Left panel: gradient with logo --}}
    <div class="d-n@sm- peer peer-greed h-100 pos-r bgr-n bgpX-c bgpY-c bgsz-cv" style="background: linear-gradient(135deg, #6366f1, #4f46e5);">
        <div class="pos-a centerXY">
            <div class="bgc-white bdrs-50p pos-r" style="width: 120px; height: 120px;">
                <img class="pos-a centerXY" src="/adminator/logo.svg" alt="" style="max-width: 60px;">
            </div>
        </div>
    </div>

    {{-- Right panel: form --}}
    <div class="col-12 col-md-4 peer pX-40 pY-80 h-100 bgc-white scrollable pos-r" style="min-width: 320px;">
        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@yield('scripts')
</body>
</html>
