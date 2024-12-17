<?php

declare(strict_types=1);


namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class VerifyController
{
    public function __construct(private readonly Twig $twig)
    {
    }

    public function index(Response $response): Response
    {
        return $this->twig->render($response,'auth/verify.twig');
    }

}