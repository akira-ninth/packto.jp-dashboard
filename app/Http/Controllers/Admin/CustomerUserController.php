<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Support\InvitationMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * 顧客に紐付いた customer ロールユーザの追加 / 削除。
 *
 * Phase 13g で「顧客作成時の初期ユーザ作成」を提供したが、後から
 * ユーザを追加 / 削除するルートが無かったので Phase 13i で追加。
 *
 * scope: master のみアクセス可 (admin.packto.jp ルート)。
 * master ロールのユーザはここでは扱わない (Phase 13h で別 controller 予定)。
 */
class CustomerUserController extends Controller
{
    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
        ]);

        $tempPassword = Str::password(16, true, true, false);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($tempPassword),
            'role' => User::ROLE_CUSTOMER,
            'customer_id' => $customer->id,
        ]);

        $mailSent = InvitationMailer::send($user, $tempPassword);

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('temp_credentials', [
                'email' => $data['email'],
                'password' => $tempPassword,
                'mail_sent' => $mailSent,
            ]);
    }

    public function destroy(Request $request, Customer $customer, User $user): RedirectResponse
    {
        // master をこの経路で削除させない (Phase 13h で別 controller 予定)
        // この check を最初に行うので、自身が master の場合も含めて先に弾かれる
        if ($user->role !== User::ROLE_CUSTOMER) {
            abort(403, 'master ユーザはこの画面から削除できません');
        }

        // 経路の整合性: URL の {customer} と user->customer_id が一致しているか
        if ($user->customer_id !== $customer->id) {
            abort(404);
        }

        // 念のため自分自身を消そうとしていないか (customer ロールが /customers/.../users
        // を叩くことは role middleware で弾かれているが、保険)
        if ($request->user()->id === $user->id) {
            abort(403, '自分自身は削除できません');
        }

        $user->delete();

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('status', "{$user->email} を削除しました");
    }
}
