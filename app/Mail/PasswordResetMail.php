<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Phase 13m: パスワードリセット用メール。
 */
class PasswordResetMail extends Mailable
{
    use Queueable;

    public function __construct(
        public readonly User $user,
        public readonly string $resetUrl,
    ) {}

    public function envelope(): Envelope
    {
        $replyToAddress = config('mail.reply_to.address');
        $replyToName = config('mail.reply_to.name');

        return new Envelope(
            subject: '[Packto] パスワードリセットのご案内',
            replyTo: $replyToAddress
                ? [new Address($replyToAddress, $replyToName ?: 'Packto')]
                : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.password_reset',
            with: [
                'name' => $this->user->name,
                'resetUrl' => $this->resetUrl,
            ],
        );
    }
}
