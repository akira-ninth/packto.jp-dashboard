<?php

namespace App\Http\Controllers;

use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * 自分のアカウント設定 (両ロール共通: master / customer 両方アクセス可)
 */
class AccountController extends Controller
{
    public function edit(): View
    {
        return view('account.edit');
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'unique:users,email,'.$request->user()->id],
            'current_password' => ['required', 'current_password'],
        ]);

        $oldEmail = $request->user()->email;
        $request->user()->update(['email' => $data['email']]);

        AuditLogger::record('auth.email_change',
            ['type' => 'user', 'id' => $request->user()->id, 'label' => $request->user()->email],
            ['old_email' => $oldEmail],
        );

        return redirect()->route('account.edit')->with('status', 'メールアドレスを変更しました。');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update([
            'password' => Hash::make($data['password']),
        ]);

        AuditLogger::record('auth.password_change',
            ['type' => 'user', 'id' => $request->user()->id, 'label' => $request->user()->email],
        );

        return redirect()->route('account.edit')->with('status', 'パスワードを変更しました。');
    }
}
