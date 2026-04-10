@extends('layouts.admin')

@section('title', 'マスター管理 | Packto Console')

@section('content')
    <h4 class="c-grey-900 mT-10 mB-30">マスターアカウント</h4>

    @if (session('temp_credentials'))
        @php $mailSent = session('temp_credentials.mail_sent'); @endphp
        <div class="bgc-white bd bdrs-3 p-20 mB-20" style="border-left: 4px solid #f59e0b;">
            <h5 class="c-grey-900 mB-15"><i class="fa fa-triangle-exclamation c-orange-500 mR-5"></i> 初期ログイン情報 (この画面でのみ表示されます)</h5>
            @if ($mailSent === true)
                <div class="alert alert-success fsz-sm pY-10 mB-15">
                    <i class="fa fa-check-circle mR-5"></i> 招待メールを <code>{{ session('temp_credentials.email') }}</code> に送信しました。
                </div>
            @elseif ($mailSent === false)
                <div class="alert alert-danger fsz-sm pY-10 mB-15">
                    <i class="fa fa-triangle-exclamation mR-5"></i> メール送信に失敗しました。下記の情報を手動で控えてください。
                </div>
            @endif
            <p class="c-grey-600 fsz-sm mB-15">
                次にこのページをリロードすると消えます。
                追加されたマスターは初回ログイン後に <code>/account</code> でパスワードを変更してください。
            </p>
            <table class="table table-sm table-borderless" style="max-width: 500px;">
                <tr><th style="width: 140px;">ログイン URL</th><td><code>https://admin.packto.jp/login</code></td></tr>
                <tr><th>メール</th><td><code>{{ session('temp_credentials.email') }}</code></td></tr>
                <tr><th>パスワード</th><td><code class="bgc-white pX-10 pY-5 bdrs-3" style="font-size: .875rem;">{{ session('temp_credentials.password') }}</code></td></tr>
            </table>
        </div>
    @endif

    {{-- Master list --}}
    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <h4 class="c-grey-900 mB-20"><i class="ti-shield mR-10 c-grey-500"></i>登録済みマスター ({{ $masters->count() }})</h4>
        <table class="table">
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
                                <form method="POST" action="{{ route('admin.masters.destroy', $master) }}" class="d-ib" onsubmit="return confirm('本当に {{ $master->email }} を削除しますか?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fa fa-trash mR-5"></i> 削除
                                    </button>
                                </form>
                            @else
                                <span class="c-grey-600" style="font-size: .75rem;">(自分)</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Add master form --}}
    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <h4 class="c-grey-900 mB-20"><i class="ti-plus mR-10 c-grey-500"></i>マスター追加</h4>
        <form method="POST" action="{{ route('admin.masters.store') }}">
            @csrf
            <div class="row gap-20">
                <div class="col-md-5">
                    <label class="form-label">名前</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                    @error('name')<div class="c-red-500 mT-5 fsz-sm">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label">メールアドレス</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                    @error('email')<div class="c-red-500 mT-5 fsz-sm">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa fa-plus"></i> 追加
                    </button>
                </div>
            </div>
            <p class="c-grey-600 mT-10 mB-0" style="font-size: .75rem;">パスワードは自動生成され、追加完了画面で 1 度だけ表示されます。</p>
        </form>
    </div>
@endsection
