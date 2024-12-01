<?php

declare(strict_types=1);

namespace App;

use App\Contracts\SessionInterface;
use App\DataObject\SessionConfig;
use App\Exception\SessionException;

class Session implements SessionInterface
{

    public function __construct(private readonly SessionConfig $options)
    {
    }

    public function start(): void
    {
        if ($this->isActive()) {
            throw new SessionException('Session has already been started');
        }

        if (headers_sent($fileName, $line)) {
            throw new SessionException('Headers already sent' . $fileName . ':' . $line);
        }

        session_set_cookie_params(
            [
                'secure' => $this->options->secure,
                'httponly' => $this->options->httpOnly,
                'samesite' => $this->options->sameSite->value,
            ]
        );

        session_start();
    }

    public function save(): void
    {
        session_write_close();
    }

    public function isActive(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }
}