<?php

declare(strict_types=1);

namespace App\Middleware;

use Doctrine\ORM\EntityManager;
use App\Entity\User;
use Doctrine\ORM\Exception\NotSupported;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthenticationMiddleware implements MiddlewareInterface
{

    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!empty($_SESSION['user']))
        {
            $user = $this->entityManager->getRepository(User::class)->find($_SESSION['user']);

            $request = $request->withAttribute('user', $user);
        }

        return $handler->handle($request);
    }
}