<?php

declare(strict_types=1);


namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class VerifyController
{
    public function __construct(private readonly Twig $twig)
    {
    }

    public function index(Response $response): Response
    {
        return $this->twig->render($response, 'auth/verify.twig');
    }

    public function verify(Request $request, Response $response): Response
    {
        return $response;
    }

}