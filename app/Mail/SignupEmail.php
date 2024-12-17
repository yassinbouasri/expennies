<?php

declare(strict_types=1);


namespace App\Mail;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class SignupEmail
{

    public function send(string $to): void
    {
        $message = (new TemplatedEmail())
        ->from($this->config->get('mail.from'))
        ->to($to)
        ->subject('Welcome to Expennies')
        ->htmlTemplate('emails/signup.html.twig')
        ->context([]);
    }
}