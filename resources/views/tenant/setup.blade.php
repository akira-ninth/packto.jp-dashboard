@extends('layouts.tenant')

@section('title', 'セットアップ | Packto')

@section('content')
    <h4 class="c-grey-900 mT-10 mB-30">セットアップ</h4>

    @if ($customer)
        {{-- .htaccess タグ --}}
        <div class="bgc-white bd bdrs-3 p-20 mB-20">
            <h5 class="c-grey-900 mB-15"><i class="ti-tag mR-10 c-grey-500"></i>.htaccess 設定タグ</h5>
            <p class="c-grey-600 fsz-sm mB-15">
                サイトの <code>.htaccess</code> に以下を追記してください。
            </p>
            <div class="pos-r">
                <pre class="bgc-grey-100 p-15 bdrs-3 mB-0" style="font-size: .8125rem; overflow-x: auto; white-space: pre-wrap;" id="htaccessCode">RewriteEngine On
RewriteCond %{HTTP_HOST} !={{ $subdomain }}.packto.jp
RewriteRule ^(.+\.(jpg|jpeg|png|gif|webp|js|mjs|css|svg|json))$ https://{{ $subdomain }}.packto.jp/$1 [R=302,L]</pre>
                <button class="btn btn-sm btn-outline-secondary pos-a" style="top: 8px; right: 8px;" onclick="copyCode('htaccessCode')">
                    <i class="fa fa-copy"></i> コピー
                </button>
            </div>
        </div>

        {{-- 動作確認 --}}
        <div class="bgc-white bd bdrs-3 p-20 mB-20">
            <h5 class="c-grey-900 mB-15"><i class="ti-check-box mR-10 c-grey-500"></i>動作確認</h5>
            <p class="c-grey-600 fsz-sm mB-15">
                .htaccess 設定後、packto CDN が正しく動作しているか確認します。<br>
                チェック対象のページ URL を入力してください。
            </p>
            <div class="row gap-20 mB-15">
                <div class="col-md-8">
                    <input type="url" id="checkUrl" class="form-control" placeholder="https://{{ $origin }}/path/to/image.jpg" value="{{ $origin }}/">
                </div>
                <div class="col-md-4">
                    <button id="checkBtn" class="btn btn-primary w-100" onclick="checkStatus()">
                        <i class="fa fa-refresh mR-5"></i> チェック実行
                    </button>
                </div>
            </div>
            <div id="checkResult" style="display: none;"></div>
        </div>
    @else
        <div class="alert alert-warning">
            <i class="fa fa-triangle-exclamation mR-5"></i> 顧客情報が紐付いていません。
        </div>
    @endif
@endsection

@section('scripts')
<script>
function copyCode(id) {
    const text = document.getElementById(id).textContent;
    navigator.clipboard.writeText(text).then(() => {
        const btn = event.target.closest('button');
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-check"></i> コピーしました';
        setTimeout(() => { btn.innerHTML = orig; }, 2000);
    });
}

function checkStatus() {
    const btn = document.getElementById('checkBtn');
    const result = document.getElementById('checkResult');
    const url = document.getElementById('checkUrl').value.trim();
    if (!url) { alert('URL を入力してください'); return; }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin mR-5"></i> チェック中...';
    result.style.display = 'none';

    fetch('{{ route("tenant.setup.check") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ url: url }),
    })
    .then(r => r.json())
    .then(data => {
        result.style.display = 'block';
        if (data.ok) {
            result.innerHTML = '<div class="alert alert-success fsz-sm mB-0">' +
                '<i class="fa fa-check-circle mR-5"></i>' + data.message + '</div>';
        } else {
            result.innerHTML = '<div class="alert alert-danger fsz-sm mB-0">' +
                '<i class="fa fa-times-circle mR-5"></i>' + data.message + '</div>';
        }
    })
    .catch(() => {
        result.style.display = 'block';
        result.innerHTML = '<div class="alert alert-danger fsz-sm mB-0">' +
            '<i class="fa fa-times-circle mR-5"></i>通信エラー</div>';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-refresh mR-5"></i> チェック実行';
    });
}
</script>
@endsection
