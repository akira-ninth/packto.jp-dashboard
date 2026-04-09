<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorService;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Phase 13n: 2FA (TOTP) 設定画面とセットアップフロー。
 *
 * GET  /two-factor/setup     → QR コード表示 + 「コードを入力して有効化」フォーム
 * POST /two-factor/setup     → secret を仮保存 (まだ confirmed_at は null)
 * POST /two-factor/confirm   → 入力 6 桁を検証 → 成功なら confirmed_at をセット + リカバリーコード生成
 * POST /two-factor/disable   → 現在のパスワード再認証 + 全フィールドクリア
 * POST /two-factor/recovery-codes → リカバリーコード再生成
 */
class TwoFactorController extends Controller
{
    public function __construct(
        private readonly TwoFactorService $tfa,
    ) {}

    public function show(Request $request): View
    {
        $user = $request->user();

        $qrDataUri = null;
        $secret = $request->session()->get('2fa.pending_secret');
        if ($secret && ! $user->hasTwoFactorEnabled()) {
            $qrDataUri = $this->tfa->generateQrCodeDataUri($user, $secret);
        }

        return view('two_factor.setup', [
            'enabled' => $user->hasTwoFactorEnabled(),
            'qrDataUri' => $qrDataUri,
            'pendingSecret' => $secret,
            'recoveryCodes' => $request->session()->get('2fa.recovery_codes_just_generated'),
        ]);
    }

    /**
     * 新しい secret を生成して session に仮保存。
     * 確認 6 桁を入れるまでは DB に保存しない。
     */
    public function setup(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.show')
                ->with('status', '2FA は既に有効です。一度無効化してから再設定してください。');
        }

        $secret = $this->tfa->generateSecret();
        $request->session()->put('2fa.pending_secret', $secret);

        return redirect()->route('two-factor.show');
    }

    /**
     * Authenticator から取得した 6 桁を検証 → 成功なら DB に保存して有効化完了
     */
    public function confirm(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $secret = $request->session()->get('2fa.pending_secret');
        if (! $secret) {
            return redirect()->route('two-factor.show')
                ->withErrors(['code' => 'セットアップのセッションが切れました。最初からやり直してください。']);
        }

        if (! $this->tfa->verify($secret, $data['code'])) {
            throw ValidationException::withMessages([
                'code' => 'コードが正しくありません。Authenticator アプリの 6 桁を入力してください。',
            ]);
        }

        $user = $request->user();
        $recoveryCodes = $this->tfa->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $request->session()->forget('2fa.pending_secret');
        $request->session()->flash('2fa.recovery_codes_just_generated', $recoveryCodes);

        AuditLogger::record('auth.2fa_enabled',
            ['type' => 'user', 'id' => $user->id, 'label' => $user->email],
        );

        return redirect()->route('two-factor.show')
            ->with('status', '2FA を有効化しました。リカバリーコードを必ず控えてください。');
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        AuditLogger::record('auth.2fa_disabled',
            ['type' => 'user', 'id' => $user->id, 'label' => $user->email],
        );

        return redirect()->route('two-factor.show')->with('status', '2FA を無効化しました。');
    }

    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.show');
        }

        $codes = $this->tfa->generateRecoveryCodes();
        $user->forceFill(['two_factor_recovery_codes' => $codes])->save();

        $request->session()->flash('2fa.recovery_codes_just_generated', $codes);

        AuditLogger::record('auth.2fa_recovery_codes_regenerated',
            ['type' => 'user', 'id' => $user->id, 'label' => $user->email],
        );

        return redirect()->route('two-factor.show');
    }
}
