<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CardPaymentSuccessful extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $active_services;
    public $payment;
    public $package;

    public function __construct($user,$active_services,$payment,$package)
    {
        $this->user = $user;
        $this->active_services = $active_services;
        $this->payment = $payment;
        $this->package = $package;
    }

    public function build()
    {
        return $this->subject('Card Payment Successful')
                    ->view('emails.card_payment_successful');
    }
}
