<?php

declare(strict_types=1);

namespace App\Http\Users\StoreUser;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * B14 — a new user chooses their password through this mail. The link is a
 * password-reset token on Fortify's reset page, so it expires as reset links
 * do (config/auth.php).
 */
final class UserInvitation extends Notification
{
    public function __construct(#[\SensitiveParameter] private readonly string $token) {}

    /**
     * @return list<string>
     */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject(__('Your account for :app', ['app' => config('app.name')]))
            ->line(__('An account has been created for you. Choose a password to sign in.'))
            ->action(__('Set password'), $url)
            ->line(__('This link expires in :count minutes.', [
                'count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
            ]));
    }
}
