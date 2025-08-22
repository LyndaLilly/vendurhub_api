<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\Mail;

class SubscriptionSuccessfulMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $plan;
    public $expiresAt;

    public function __construct($user, $plan, $expiresAt)
    {
        $this->user = $user;
        $this->plan = $plan;
        $this->expiresAt = $expiresAt;
    }

    public function build()
    {
        return $this->subject('Your Subscription is Active!')
            ->view('emails.subscription_success');
    }
}
