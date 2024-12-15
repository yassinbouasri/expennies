<?php

declare(strict_types=1);


namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;

class EntityManagerService
{
    public function __construct(protected readonly EntityManagerInterface $entityManager)
    {
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }

}