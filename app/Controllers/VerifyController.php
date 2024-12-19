<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Contracts\UserProviderServiceInterface;
use App\Entity\User;
use App\Mail\SignupEmail;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class VerifyController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly UserProviderServiceInterface $userProviderService,
        private readonly SignupEmail $signupEmail,
    ){
    }

    public function index(ResponseInterface $response, Request $request): ResponseInterface
    {
        $user = $request->getAttribute('user');
        return $this->twig->render($response, 'auth/verify.twig');
    }
    public function verify(Request $request, ResponseInterface $response, array $args): ResponseInterface
    {
        /** @var User $user $user */
        $user = $request->getAttribute('user');

        if(! hash_equals((string) $user->getId(), $args['id'] ) || ! hash_equals(sha1($user->getEmail()), $args['hash'])) {
            throw new \RuntimeException('Verification failed');
        }

        if (! $user->getVerifiedAt()){
            $this->userProviderService->verifyUser($user);
        }


        return $response->withHeader('Location', '/')->withStatus(302);
    }
    public function resend(Request $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $user = $request->getAttribute('user');
        $this->signupEmail->send($user);
        return $response->withHeader('Location', '/')->withStatus(302);
    }
}
