<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Packto ログイン情報のご案内</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Hiragino Kaku Gothic ProN', Meiryo, sans-serif; background: #f7f7f9; margin: 0; padding: 24px; color: #1f2937;">
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width: 560px; margin: 0 auto;">
        <tr>
            <td style="padding: 24px 0 16px;">
                <h1 style="margin: 0; font-size: 20px; color: #1f2937;">Packto Console ログイン情報</h1>
            </td>
        </tr>
        <tr>
            <td style="background: #fff; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <p style="margin: 0 0 16px;">{{ $name }} 様</p>

                <p style="margin: 0 0 16px; line-height: 1.7;">
                    Packtoのアカウントが作成されました。<br>
                    下記の情報でログインし、初回ログイン後に必ずパスワードを変更してください。
                </p>

                <table cellpadding="8" cellspacing="0" border="0" width="100%" style="border-collapse: collapse; margin: 16px 0;">
                    <tr>
                        <td style="border-bottom: 1px solid #e5e7eb; color: #6b7280; width: 140px; font-size: 13px;">ログイン URL</td>
                        <td style="border-bottom: 1px solid #e5e7eb;"><a href="{{ $loginUrl }}" style="color: #2563eb; text-decoration: none;">{{ $loginUrl }}</a></td>
                    </tr>
                    <tr>
                        <td style="border-bottom: 1px solid #e5e7eb; color: #6b7280; font-size: 13px;">メールアドレス</td>
                        <td style="border-bottom: 1px solid #e5e7eb;"><code style="background: #f3f4f6; padding: 2px 6px; border-radius: 4px;">{{ $email }}</code></td>
                    </tr>
                    <tr>
                        <td style="border-bottom: 1px solid #e5e7eb; color: #6b7280; font-size: 13px;">初期パスワード</td>
                        <td style="border-bottom: 1px solid #e5e7eb;"><code style="background: #fef3c7; padding: 4px 8px; border-radius: 4px; font-size: 14px; font-weight: 600;">{{ $tempPassword }}</code></td>
                    </tr>
                </table>

                <p style="margin: 16px 0; font-size: 13px; color: #92400e; background: #fef3c7; padding: 12px; border-radius: 6px;">
                    ⚠️ <strong>セキュリティ上のお願い:</strong><br>
                    初期パスワードは仮パスワードです。ログイン後、画面右上の「アカウント」メニューから新しいパスワードに変更してください。
                </p>

                <p style="margin: 16px 0 0; font-size: 12px; color: #9ca3af;">
                    お心当たりが無い場合や不明点がある場合は、このメールへの返信でお問い合わせください。
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 16px 8px; font-size: 11px; color: #9ca3af; text-align: center;">
                Packto Console — packto.jp
            </td>
        </tr>
    </table>
</body>
</html>
