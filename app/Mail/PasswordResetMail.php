<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class PasswordResetMail extends Mailable
{
    public $reset_url;
    public $seller;

    public function __construct($reset_url, $seller)
    {
        $this->reset_url = $reset_url;
        $this->seller = $seller;
    }

    public function build()
    {
        return $this->mailer('customer')
            ->from(
                config('mail.mailers.customer.username'),
                'InteriorChowk Support'
            )
            ->subject(__('Password Reset'))
            ->view('email-templates.admin-password-reset', [
                'url'    => $this->reset_url,
                'seller' => $this->seller,
            ]);
    }
}