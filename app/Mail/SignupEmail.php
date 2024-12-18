<?php

declare(strict_types=1);


namespace App\Mail;

use App\Config;
use Slim\Interfaces\RouteParserInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\BodyRendererInterface;

class SignupEmail
{
    public
    function __construct(
        private readonly Config                $config,
        private readonly MailerInterface       $mailer,
        private readonly BodyRendererInterface $bodyRenderer,
        private readonly RouteParserInterface $routeParser,
    ){
    }

    public function send(string $to): void
    {
        $activationLink = $this->generateSignedUrl();

        $message = (new TemplatedEmail())
        ->from($this->config->get('mailer.from'))
        ->to($to)
        ->subject('Welcome to Expennies')
        ->htmlTemplate('emails/signup.html.twig')
        ->context([
            'activationLink' => $activationLink,
            'expirationDate' => new \DateTime('+30 minutes')
        ]);

        $this->bodyRenderer->render($message);

        $this->mailer->send($message);
    }

    private function generateSignedUrl(int $userId, string $email, \DateTime $expirationDate)
    {
        $expiration = $expirationDate->getTimestamp();
        $routeParams = ['id' => $userId, 'hash' => sha1($email)];
        $queryParams = ['expiration' => $expiration];
        $baseUrl = trim($this->config->get('app.url'), '/');
        $url = $baseUrl . $this->routeParser->urlFor('verify', $routeParams, $queryParams);

        $singatue = hash_hmac('sha256', $url);

    }
}