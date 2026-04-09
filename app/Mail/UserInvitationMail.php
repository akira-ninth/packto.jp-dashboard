<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * 招待メール (Phase 13j)。
 *
 * 顧客追加 / customer ユーザ追加 / master 追加の 3 経路から発火する。
 * 受信側で初回ログインに必要な URL とテンポラリパスワードを案内する。
 *
 * SerializesModels は外している (テンポラリパスワードを passing するので
 * シリアライズして queue に積むと永続化されてしまう懸念。同期送信のみ前提)。
 */
class UserInvitationMail extends Mailable
{
    use Queueable;

    public function __construct(
        public readonly User $user,
        public readonly string $tempPassword,
        public readonly string $loginUrl,
    ) {}

    public function envelope(): Envelope
    {
        $replyToAddress = config('mail.reply_to.address');
        $replyToName = config('mail.reply_to.name');

        return new Envelope(
            subject: '[Packto] '.$this->user->name.' 様 ログイン情報のご案内',
            replyTo: $replyToAddress
                ? [new Address($replyToAddress, $replyToName ?: 'Packto')]
                : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.user_invitation',
            with: [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'role' => $this->user->role,
                'tempPassword' => $this->tempPassword,
                'loginUrl' => $this->loginUrl,
            ],
        );
    }
}
