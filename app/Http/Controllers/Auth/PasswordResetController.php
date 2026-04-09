<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Phase 13m: パスワードリセット (セルフサービス)。
 *
 * フロー:
 *   GET  /password/forgot           → メールアドレス入力フォーム
 *   POST /password/forgot           → トークン生成 + メール送信
 *   GET  /password/reset/{token}    → 新パスワード入力フォーム (token + email クエリ)
 *   POST /password/reset            → トークン検証 + パスワード更新
 *
 * セキュリティ方針:
 * - email 列挙を防ぐため、登録の有無に関わらず常に同じ「メール送信しました」メッセージを返す
 * - rate limit: 同一 IP 5 回 / 15 分
 * - トークンは Laravel 標準の password_reset_tokens テーブル経由で 60 分有効
 * - 受信ロールは master / customer どちらにも対応 (admin/app 両ログインから来る)
 * - リセット成功時は audit log に記録
 */
class PasswordResetController extends Controller
{
    private const REQUEST_LIMIT = 5;
    private const REQUEST_DECAY = 900; // 15 分

    public function showForgotForm(): View
    {
        return view('auth.password_forgot');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        // rate limit (IP 単位)
        $key = 'password-forgot:'.($request->header('CF-Connecting-IP') ?: $request->ip());
        if (RateLimiter::tooManyAttempts($key, self::REQUEST_LIMIT)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "リクエストが多すぎます。{$seconds} 秒後に再試行してください。",
            ]);
        }
        RateLimiter::hit($key, self::REQUEST_DECAY);

        $user = User::where('email', $data['email'])->first();
        if ($user) {
            $token = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now(),
                ],
            );

            $resetUrl = $this->buildResetUrl($user, $token);
            try {
                Mail::to($user->email)->send(new PasswordResetMail($user, $resetUrl));
            } catch (\Throwable $e) {
                Log::warning('Password reset mail failed', ['email' => $user->email, 'error' => $e->getMessage()]);
            }

            AuditLogger::record('auth.password_reset_requested',
                ['type' => 'user', 'id' => $user->id, 'label' => $user->email],
            );
        }

        // 列挙防止: 登録の有無に関わらず同じメッセージ
        return redirect()->route('password.forgot')
            ->with('status', '登録があれば、リセット用リンクをメールで送信しました。受信箱を確認してください。');
    }

    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.password_reset', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $row = DB::table('password_reset_tokens')->where('email', $data['email'])->first();
        if (! $row || ! Hash::check($data['token'], $row->token)) {
            throw ValidationException::withMessages([
                'email' => 'このリセットリンクは無効か、すでに使用済みです。',
            ]);
        }

        // 60 分有効 (Carbon 3 で diffInMinutes が signed なので abs を取る)
        $createdAt = \Carbon\Carbon::parse($row->created_at);
        $minutesAgo = (int) abs(now()->diffInMinutes($createdAt, absolute: true));
        if ($minutesAgo > 60) {
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
            throw ValidationException::withMessages([
                'email' => 'このリセットリンクは有効期限切れです。再度リクエストしてください。',
            ]);
        }

        $user = User::where('email', $data['email'])->firstOrFail();
        $user->update(['password' => Hash::make($data['password'])]);

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        AuditLogger::record('auth.password_reset_completed',
            ['type' => 'user', 'id' => $user->id, 'label' => $user->email],
        );

        return redirect()->route('login')->with('status', 'パスワードを更新しました。新しいパスワードでログインしてください。');
    }

    /**
     * 受信ロールに応じてリセット URL のドメインを切り替える。
     * master → admin.packto.jp、customer → app.packto.jp
     */
    private function buildResetUrl(User $user, string $token): string
    {
        $domain = $user->isMaster()
            ? config('app.admin_domain')
            : config('app.app_domain');
        $email = urlencode($user->email);
        return "https://{$domain}/password/reset/{$token}?email={$email}";
    }
}
