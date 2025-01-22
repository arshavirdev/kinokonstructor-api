<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $actionUrl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $actionUrl)
    {
        $this->to($user);
        $this->subject('Сброс пароля');
        $this->user = $user;
        $this->actionUrl = $actionUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mail.reset-password', ['user' => $this->user, 'verification_url' => $this->actionUrl]);
    }
}
