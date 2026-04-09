<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\InvitationMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * master ロールユーザの管理 (Phase 13h)。
 *
 * 統合管理画面 (admin.packto.jp) にログインできる master アカウントを追加 / 削除する。
 * 顧客 (customer) ロールユーザは Phase 13i の AdminCustomerUserController が扱う。
 *
 * セーフガード:
 * - 自分自身は削除できない
 * - 最後の 1 人 (=システム上 master が居なくなる) は削除できない
 */
class MasterController extends Controller
{
    public function index(): View
    {
        return view('admin.masters.index', [
            'masters' => User::where('role', User::ROLE_MASTER)->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
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
            'role' => User::ROLE_MASTER,
            'customer_id' => null,
        ]);

        $mailSent = InvitationMailer::send($user, $tempPassword);

        AuditLogger::record('master.create',
            ['type' => 'user', 'id' => $user->id, 'label' => $user->email],
            ['mail_sent' => $mailSent],
        );

        return redirect()
            ->route('admin.masters.index')
            ->with('temp_credentials', [
                'email' => $data['email'],
                'password' => $tempPassword,
                'mail_sent' => $mailSent,
            ]);
    }

    public function destroy(Request $request, User $master): RedirectResponse
    {
        // 自分自身は削除させない
        if ($request->user()->id === $master->id) {
            abort(403, '自分自身は削除できません。他のマスターアカウントからログインして削除してください。');
        }

        // master ロール以外をこの経路で消させない
        if ($master->role !== User::ROLE_MASTER) {
            abort(404);
        }

        // 最後の 1 人を残す
        $remaining = User::where('role', User::ROLE_MASTER)->where('id', '!=', $master->id)->count();
        if ($remaining === 0) {
            abort(403, '最後のマスターアカウントは削除できません');
        }

        $email = $master->email;
        $masterId = $master->id;
        $master->delete();

        AuditLogger::record('master.delete',
            ['type' => 'user', 'id' => $masterId, 'label' => $email],
        );

        return redirect()
            ->route('admin.masters.index')
            ->with('status', "{$email} を削除しました");
    }
}
