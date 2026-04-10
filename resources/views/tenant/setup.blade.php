@extends('layouts.tenant')

@section('title', 'セットアップ | Packto')

@section('content')
    <h4 class="c-grey-900 mT-10 mB-30">セットアップ</h4>

    @if ($customer)
        {{-- Packto 有効チェック --}}
        <div class="bgc-white bd bdrs-3 p-20 mB-20">
            <h5 class="c-grey-900 mB-15"><i class="ti-check-box mR-10 c-grey-500"></i>配信ステータス確認</h5>
            <p class="c-grey-600 fsz-sm mB-15">
                <code>{{ $subdomain }}.packto.jp</code> 経由で CDN が動作しているかチェックします。
            </p>
            <button id="checkBtn" class="btn btn-primary btn-sm" onclick="checkStatus()">
                <i class="fa fa-refresh mR-5"></i> チェック実行
            </button>
            <div id="checkResult" class="mT-15" style="display: none;"></div>
        </div>

        {{-- .htaccess タグ --}}
        <div class="bgc-white bd bdrs-3 p-20 mB-20">
            <h5 class="c-grey-900 mB-15"><i class="ti-tag mR-10 c-grey-500"></i>.htaccess 設定タグ</h5>
            <p class="c-grey-600 fsz-sm mB-15">
                サイトの <code>.htaccess</code> に以下のルールを追加すると、画像と静的ファイルが packto CDN 経由で配信されます。
                <code>RewriteEngine On</code> の後に追記してください。
            </p>
            <div class="pos-r">
                <pre class="bgc-grey-100 p-15 bdrs-3 mB-0" style="font-size: .8125rem; overflow-x: auto; white-space: pre-wrap;" id="htaccessCode">RewriteCond %{HTTP_HOST} !={{ $subdomain }}.packto.jp
RewriteRule ^(.+\.(jpg|jpeg|png|gif|webp|js|mjs|css|svg|json))$ https://{{ $subdomain }}.packto.jp/$1 [R=302,L]</pre>
                <button class="btn btn-sm btn-outline-secondary pos-a" style="top: 8px; right: 8px;" onclick="copyCode('htaccessCode')">
                    <i class="fa fa-copy"></i> コピー
                </button>
            </div>
            <div class="alert alert-info fsz-sm mT-15 mB-0">
                <i class="fa fa-info-circle mR-5"></i>
                <strong>注意:</strong> Origin サーバ (<code>{{ $origin }}</code>) の <code>.htaccess</code> に追加してください。
                packto 側には何も設定不要です。
            </div>
        </div>

        {{-- 確認方法 --}}
        <div class="bgc-white bd bdrs-3 p-20 mB-20">
            <h5 class="c-grey-900 mB-15"><i class="ti-help-alt mR-10 c-grey-500"></i>動作確認方法</h5>
            <p class="c-grey-600 fsz-sm mB-10">
                .htaccess を設定した後、ブラウザの開発者ツール (Network タブ) で画像リクエストを確認してください:
            </p>
            <ol class="c-grey-600 fsz-sm">
                <li>サイトにアクセスして Network タブを開く</li>
                <li>画像ファイル (jpg, png 等) のリクエストを選択</li>
                <li>Response Headers に <code>x-imagy-version</code> があれば CDN 経由で配信されています</li>
                <li>または上の「チェック実行」ボタンで自動確認できます</li>
            </ol>
        </div>

        {{-- curl で確認 --}}
        <div class="bgc-white bd bdrs-3 p-20 mB-20">
            <h5 class="c-grey-900 mB-15"><i class="ti-panel mR-10 c-grey-500"></i>curl で確認</h5>
            <div class="pos-r">
                <pre class="bgc-grey-100 p-15 bdrs-3 mB-0" style="font-size: .8125rem; overflow-x: auto;" id="curlCode">curl -sI "https://{{ $subdomain }}.packto.jp/path/to/image.jpg" | grep x-imagy</pre>
                <button class="btn btn-sm btn-outline-secondary pos-a" style="top: 8px; right: 8px;" onclick="copyCode('curlCode')">
                    <i class="fa fa-copy"></i> コピー
                </button>
            </div>
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
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin mR-5"></i> チェック中...';
    result.style.display = 'none';

    fetch('{{ route("tenant.setup.check") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
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
