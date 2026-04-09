<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Packto パスワードリセット</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Hiragino Kaku Gothic ProN', Meiryo, sans-serif; background: #f7f7f9; margin: 0; padding: 24px; color: #1f2937;">
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width: 560px; margin: 0 auto;">
        <tr>
            <td style="padding: 24px 0 16px;">
                <h1 style="margin: 0; font-size: 20px;">Packto Console パスワードリセット</h1>
            </td>
        </tr>
        <tr>
            <td style="background: #fff; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <p style="margin: 0 0 16px;">{{ $name }} 様</p>

                <p style="margin: 0 0 16px; line-height: 1.7;">
                    パスワードリセットのリクエストを受け付けました。<br>
                    下記の URL から新しいパスワードを設定してください。<strong>このリンクは 60 分間有効</strong>です。
                </p>

                <p style="text-align: center; margin: 24px 0;">
                    <a href="{{ $resetUrl }}" style="display: inline-block; padding: 12px 24px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600;">パスワードを再設定する</a>
                </p>

                <p style="margin: 16px 0; font-size: 12px; color: #6b7280; word-break: break-all;">
                    リンクが押せない場合は下記の URL をブラウザに貼り付けてください:<br>
                    <code style="background: #f3f4f6; padding: 4px; border-radius: 3px;">{{ $resetUrl }}</code>
                </p>

                <p style="margin: 16px 0; font-size: 13px; color: #92400e; background: #fef3c7; padding: 12px; border-radius: 6px;">
                    ⚠️ <strong>心当たりが無い場合:</strong><br>
                    このメールを無視してください。リンクは使用されない限り 60 分後に失効します。
                </p>

                <p style="margin: 16px 0 0; font-size: 12px; color: #9ca3af;">
                    お問い合わせは support@packto.jp まで。
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
