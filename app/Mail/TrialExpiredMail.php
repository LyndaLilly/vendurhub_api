<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrialExpiredMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your Free Trial Has Ended – Upgrade Today')
                    ->view('emails.trial_expired')
                    ->with([
                        'name' => $this->user->name,
                        'subscribeUrl' => 'https://vendurhub.com/vendor/subscription',
                    ]);
    }
}
