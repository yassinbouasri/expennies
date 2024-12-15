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

    public function clear(string $entityName = null)
    {

        if ($entityName === null) {
            $this->entityManager->clear();
        }

        $uniyOfWork = $this->entityManager->getUnitOfWork();
        $entities = $uniyOfWork->getIdentityMap()[$entityName] ?? [];

        foreach ($entities as $entity) {
            $this->entityManager->remove($entity);
        }
    }

}