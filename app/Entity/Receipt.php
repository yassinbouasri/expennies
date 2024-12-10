<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\HasTimestamps;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table('receipts')]
#[HasLifecycleCallbacks]
class Receipt
{
    use HasTimestamps;

    #[Id, Column(options: ['unsigned' => true]), GeneratedValue]
    private int $id;

    #[Column]
    private string $file_name;

    #[Column(name: 'created_at')]
    private \DateTime $createdAt;

    #[ManyToOne(inversedBy: 'receipts')]
    private Transaction $transaction;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFileName(): string
    {
        return $this->file_name;
    }

    public function setFileName(string $file_name): Receipt
    {
        $this->file_name = $file_name;

        return $this;
    }

    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(Transaction $transaction): Receipt
    {
        $transaction->addReceipt($this);

        $this->transaction = $transaction;

        return $this;
    }
}
