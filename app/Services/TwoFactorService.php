<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * Phase 13n: TOTP (Google Authenticator 互換) を扱うサービス。
 *
 * - generateSecret: 新しい base32 secret を発行 (32 文字)
 * - generateQrCodeDataUri: otpauth:// URI を data:image/png として返す (img タグの src 用)
 * - verify: 6 桁コードを検証 (前後 1 window 許容、つまり ±30 秒)
 * - generateRecoveryCodes: 8 桁 6 個 (XXXXXXXX 形式)
 * - useRecoveryCode: 使ったら配列から削除して保存
 */
class TwoFactorService
{
    public function __construct(
        private readonly Google2FA $google2fa = new Google2FA(),
    ) {}

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey(32);
    }

    /**
     * Authenticator アプリ用の otpauth URL を返す
     */
    public function otpauthUrl(User $user, string $secret): string
    {
        $issuer = config('app.name', 'Packto');
        return $this->google2fa->getQRCodeUrl($issuer, $user->email, $secret);
    }

    /**
     * QR コードを data:image/png base64 で返す (Blade で <img src="..."> に貼れる)
     */
    public function generateQrCodeDataUri(User $user, string $secret): string
    {
        $url = $this->otpauthUrl($user, $secret);
        $renderer = new GDLibRenderer(220);
        $writer = new Writer($renderer);
        $png = $writer->writeString($url);
        return 'data:image/png;base64,'.base64_encode($png);
    }

    public function verify(string $secret, string $code): bool
    {
        // window=1 で前後 30 秒許容 (時刻ズレ対策)
        return $this->google2fa->verifyKey($secret, $code, 1);
    }

    /**
     * @return list<string>
     */
    public function generateRecoveryCodes(int $count = 6): array
    {
        return collect()->range(1, $count)
            ->map(fn () => Str::upper(Str::random(4)).'-'.Str::upper(Str::random(4)))
            ->all();
    }

    /**
     * 入力された code がリカバリーコードに含まれていれば、そのコードを消費して true を返す。
     *
     * @param  list<string>  $codes
     * @return list<string>|null  null = 不一致、配列 = 消費後の残り
     */
    public function consumeRecoveryCode(array $codes, string $input): ?array
    {
        $normalized = Str::upper(trim($input));
        $idx = array_search($normalized, $codes, true);
        if ($idx === false) {
            return null;
        }
        unset($codes[$idx]);
        return array_values($codes);
    }
}
