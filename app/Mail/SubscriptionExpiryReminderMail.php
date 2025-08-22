<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class SubscriptionExpiryReminderMail extends Mailable
{
    use SerializesModels;

    public $user;
    public $expiresAt;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->expiresAt = $user->subscription_expires_at;
    }

    public function build()
    {
        return $this->subject('Your subscription is about to expire')
                    ->view('emails.subscription_expiry_reminder');
    }
}
