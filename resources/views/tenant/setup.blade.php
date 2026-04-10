@extends('layouts.tenant')

@section('title', 'セットアップ | Packto')

@section('content')
    <h4 class="c-grey-900 mT-10 mB-30">セットアップ</h4>

    @if ($customer)
        {{-- サーバー検出結果 --}}
        @if ($serverType)
            <div class="alert alert-info fsz-sm mB-20">
                <i class="fa fa-server mR-5"></i>
                <code>{{ $origin }}</code> のサーバーは
                <strong>{{ strtoupper($serverType) }}</strong> と検出されました。
                該当するタブの設定を使用してください。
            </div>
        @endif

        {{-- インストール方法 --}}
        <div class="bgc-white bd bdrs-3 p-20 mB-20">
            <h5 class="c-grey-900 mB-15"><i class="ti-tag mR-10 c-grey-500"></i>インストール方法</h5>

            {{-- Tab navigation --}}
            <ul class="nav nav-tabs mB-15" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $serverType === 'nginx' ? '' : 'active' }}" data-bs-toggle="tab" href="#tab-apache" role="tab">
                        Apache (.htaccess)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $serverType === 'nginx' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-nginx" role="tab">
                        Nginx
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                {{-- Apache --}}
                <div class="tab-pane fade {{ $serverType === 'nginx' ? '' : 'show active' }}" id="tab-apache" role="tabpanel">
                    <p class="c-grey-600 fsz-sm mB-10">
                        サイトのルートディレクトリにある <code>.htaccess</code> に以下を追記してください。
                    </p>
                    <div class="pos-r" style="max-width: 700px;">
                        <pre class="bgc-grey-100 p-15 bdrs-3 mB-0" style="font-size: .8125rem; overflow-x: auto; white-space: pre-wrap;" id="apacheCode">RewriteEngine On
RewriteCond %{HTTP_HOST} !={{ $subdomain }}.packto.jp
RewriteRule ^(.+\.(jpg|jpeg|png|gif|webp|js|mjs|css|svg|json))$ https://{{ $subdomain }}.packto.jp/$1 [R=302,L]</pre>
                        <button class="btn btn-sm btn-outline-secondary pos-a" style="top: 8px; right: 8px;" onclick="copyCode('apacheCode')">
                            <i class="fa fa-copy"></i> コピー
                        </button>
                    </div>
                    <div class="mT-15 p-15 bgc-grey-100 bdrs-3 fsz-sm c-grey-700" style="max-width: 700px;">
                        <i class="fa fa-info-circle mR-5 c-blue-500"></i>
                        <strong>WordPress をお使いの場合:</strong>
                        WordPress は <code>.htaccess</code> を自動生成します。上記のルールは WordPress の
                        <code># BEGIN WordPress</code> ブロックの<strong>前</strong>に追記してください。
                        また、子ディレクトリにも <code>.htaccess</code> が存在する場合があります。
                        ルートの <code>.htaccess</code> に追記すればサイト全体に適用されます。
                    </div>
                </div>

                {{-- Nginx --}}
                <div class="tab-pane fade {{ $serverType === 'nginx' ? 'show active' : '' }}" id="tab-nginx" role="tabpanel">
                    <p class="c-grey-600 fsz-sm mB-10">
                        Nginx の設定ファイル (通常 <code>/etc/nginx/sites-available/</code>) の <code>server</code> ブロック内に以下を追記してください。
                    </p>
                    <div class="pos-r" style="max-width: 700px;">
                        <pre class="bgc-grey-100 p-15 bdrs-3 mB-0" style="font-size: .8125rem; overflow-x: auto; white-space: pre-wrap;" id="nginxCode">location ~* \.(jpg|jpeg|png|gif|webp|js|mjs|css|svg|json)$ {
    return 302 https://{{ $subdomain }}.packto.jp$request_uri;
}</pre>
                        <button class="btn btn-sm btn-outline-secondary pos-a" style="top: 8px; right: 8px;" onclick="copyCode('nginxCode')">
                            <i class="fa fa-copy"></i> コピー
                        </button>
                    </div>
                    <p class="c-grey-600 fsz-sm mT-15">
                        設定変更後は <code>sudo nginx -t && sudo systemctl reload nginx</code> を実行してください。
                    </p>
                </div>
            </div>
        </div>

        {{-- 動作確認 --}}
        <div class="bgc-white bd bdrs-3 p-20 mB-20">
            <h5 class="c-grey-900 mB-15"><i class="ti-check-box mR-10 c-grey-500"></i>動作確認</h5>
            <p class="c-grey-600 fsz-sm mB-15">
                設定後、画像やCSS等の URL を入力してリダイレクトが正しく動作しているか確認します。
            </p>
            <div class="peers ai-c fxw-nw gap-20 mB-15" style="max-width: 600px;">
                <div class="peer peer-greed">
                    <input type="url" id="checkUrl" class="form-control form-control-sm" placeholder="https://{{ str_replace('https://', '', $origin) }}/image.jpg" value="{{ $origin }}/">
                </div>
                <div class="peer">
                    <button id="checkBtn" class="btn btn-primary btn-sm" style="white-space: nowrap;" onclick="checkStatus()">
                        <i class="fa fa-refresh mR-5"></i> チェック
                    </button>
                </div>
            </div>
            <div id="checkResult" style="display: none; max-width: 600px;"></div>
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
        const msg = data.message.replace(/\n/g, '<br>');
        if (data.ok) {
            result.innerHTML = '<div class="alert alert-success fsz-sm mB-0">' +
                '<i class="fa fa-check-circle mR-5"></i>' + msg + '</div>';
        } else {
            result.innerHTML = '<div class="alert alert-danger fsz-sm mB-0">' +
                '<i class="fa fa-times-circle mR-5"></i>' + msg + '</div>';
        }
    })
    .catch(() => {
        result.style.display = 'block';
        result.innerHTML = '<div class="alert alert-danger fsz-sm mB-0">' +
            '<i class="fa fa-times-circle mR-5"></i>通信エラー</div>';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-refresh mR-5"></i> チェック';
    });
}
</script>
@endsection
