<?php

declare(strict_types=1);


namespace App\Services;

use App\Entity\Receipt;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManager;

class ReceiptService
{

    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function create(Transaction $transaction, ?string $filename)
    {
        $receipt = new Receipt();

        $receipt->setTransaction($transaction);
        $receipt->setFilename($filename);
        $this->entityManager->persist($receipt);
        $this->entityManager->flush();

        return $receipt;
    }
}