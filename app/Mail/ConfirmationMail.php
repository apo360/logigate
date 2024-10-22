<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp; // A variável para armazenar o código OTP

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($otp)
    {
        // Atribuir o OTP passado para a variável de classe
        $this->otp = $otp;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Confirmação de Conta - Seu Código OTP')
                    ->view('emails.confirmation') // O nome da view de e-mail
                    ->with([
                        'otp' => $this->otp, // Passando o OTP para a view
                    ]);
    }
}
