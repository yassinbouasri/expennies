<?php

declare(strict_types = 1);

namespace App\Middleware;

use App\Config;
use App\Contracts\AuthInterface;
use App\Contracts\EntityManagerServiceInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Views\Twig;

class ValidateSignatureMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Config $config,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        $queryParams = $request->getQueryParams();
        $originalSignature = $queryParams['signature'] ?? null;
        $expiration = (int) $queryParams['expiration'] ?? null;

        unset($queryParams['signature']);

        $url = (string) $uri->getPath(http_build_query($queryParams));

        $signature = hash_hmac('sha256', (string) $url, $this->config->get('app_key'));

        if($expiration <= time() || !hash_equals($signature, $originalSignature)) {
            throw new \RuntimeException('Invalid signature');
        }
        return $handler->handle($request);
    }
}
