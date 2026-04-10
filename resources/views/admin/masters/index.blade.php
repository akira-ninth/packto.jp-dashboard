@extends('layouts.admin')

@section('title', 'マスター管理 | Packto Console')

@section('content')
    <h1 class="h4 fw-bold mb-4">マスターアカウント</h1>

    @if (session('temp_credentials'))
        @php $mailSent = session('temp_credentials.mail_sent'); @endphp
        <div class="block block-rounded" style="border-left: 4px solid #f59e0b;">
            <div class="block-header block-header-default" style="background: #fffbeb;">
                <h3 class="block-title"><i class="fa fa-triangle-exclamation text-warning me-1"></i> 初期ログイン情報 (この画面でのみ表示されます)</h3>
            </div>
            <div class="block-content" style="background: #fffbeb;">
                @if ($mailSent === true)
                    <div class="alert alert-success py-2 mb-3 fs-sm">
                        <i class="fa fa-check-circle me-1"></i> 招待メールを <code>{{ session('temp_credentials.email') }}</code> に送信しました。
                    </div>
                @elseif ($mailSent === false)
                    <div class="alert alert-danger py-2 mb-3 fs-sm">
                        <i class="fa fa-triangle-exclamation me-1"></i> メール送信に失敗しました。下記の情報を手動で控えてください。
                    </div>
                @endif
                <p class="mb-3 fs-sm text-muted">
                    次にこのページをリロードすると消えます。
                    追加されたマスターは初回ログイン後に <code>/account</code> でパスワードを変更してください。
                </p>
                <table class="table table-sm table-borderless table-vcenter mb-0" style="max-width: 500px;">
                    <tr><th style="width: 140px;">ログイン URL</th><td><code>https://admin.packto.jp/login</code></td></tr>
                    <tr><th>メール</th><td><code>{{ session('temp_credentials.email') }}</code></td></tr>
                    <tr><th>パスワード</th><td><code class="bg-white px-2 py-1 rounded" style="font-size: .875rem;">{{ session('temp_credentials.password') }}</code></td></tr>
                </table>
            </div>
        </div>
    @endif

    {{-- Master list --}}
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title"><i class="fa fa-shield-halved me-2 opacity-50"></i>登録済みマスター ({{ $masters->count() }})</h3>
        </div>
        <div class="block-content p-0">
            <table class="table table-hover table-vcenter mb-0">
                <thead>
                    <tr><th>名前</th><th>メール</th><th>作成日</th><th class="text-end"></th></tr>
                </thead>
                <tbody>
                    @foreach ($masters as $master)
                        <tr>
                            <td>{{ $master->name }}</td>
                            <td>{{ $master->email }}</td>
                            <td>{{ $master->created_at?->format('Y-m-d') }}</td>
                            <td class="text-end">
                                @if ($master->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.masters.destroy', $master) }}" class="d-inline" onsubmit="return confirm('本当に {{ $master->email }} を削除しますか?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-alt-danger">
                                            <i class="fa fa-trash me-1"></i> 削除
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted" style="font-size: .75rem;">(自分)</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add master form --}}
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title"><i class="fa fa-user-plus me-2 opacity-50"></i>マスター追加</h3>
        </div>
        <div class="block-content">
            <form method="POST" action="{{ route('admin.masters.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">名前</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                        @error('name')<div class="text-danger mt-1 fs-sm">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">メールアドレス</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                        @error('email')<div class="text-danger mt-1 fs-sm">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-alt-primary w-100">
                            <i class="fa fa-plus"></i> 追加
                        </button>
                    </div>
                </div>
                <p class="text-muted mt-2 mb-0" style="font-size: .75rem;">パスワードは自動生成され、追加完了画面で 1 度だけ表示されます。</p>
            </form>
        </div>
    </div>
@endsection
