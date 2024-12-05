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



}