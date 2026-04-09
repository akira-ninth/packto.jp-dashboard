<?php

namespace App\Support;

use App\Mail\UserInvitationMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Phase 13j: 招待メールを送る共通ヘルパ。
 *
 * 3 つの controller (CustomerController#store, CustomerUserController#store,
 * MasterController#store) から呼ばれる。失敗時は warn ログ + false を返し、
 * 呼び出し側で UI の temp_credentials カードに fall back する。
 */
class InvitationMailer
{
    public static function send(User $user, string $tempPassword): bool
    {
        $loginUrl = $user->isMaster()
            ? 'https://'.config('app.admin_domain').'/login'
            : 'https://'.config('app.app_domain').'/login';

        try {
            Mail::to($user->email)->send(new UserInvitationMail($user, $tempPassword, $loginUrl));
            return true;
        } catch (\Throwable $e) {
            Log::warning('Invitation mail failed', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
