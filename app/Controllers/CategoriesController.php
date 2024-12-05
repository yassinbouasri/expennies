<?php

declare(strict_types=1);


namespace App\Controllers;

use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CategoriesController
{
    public function __construct(private readonly Twig $twig)
    {
    }

    public function index(Request $request,Response $response): Response
    {
        return $this->twig->render($response,'categories/index.twig');
    }

    public function store(Request $request,Response $response): Response
    {
        return $response->withHeader('Location', '/categories')->withStatus(302);
    }

    public function delete(Request $request,Response $response): Response
    {
        return $response->withHeader('Location', '/categories')->withStatus(302);
    }
}