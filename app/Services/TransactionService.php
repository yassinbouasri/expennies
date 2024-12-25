<?php

declare(strict_types = 1);

namespace App\Services;

use App\Contracts\EntityManagerServiceInterface;
use App\DataObjects\DataTableQueryParams;
use App\DataObjects\TransactionData;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class TransactionService
{
    public function __construct(private readonly EntityManagerServiceInterface $entityManager, private readonly CacheInterface $cache)
    {
    }

    public function create(TransactionData $transactionData, User $user): Transaction
    {
        $transaction = new Transaction();

        $transaction->setUser($user);

        return $this->update($transaction, $transactionData,  $user->getId());
    }

    public function getPaginatedTransactions(DataTableQueryParams $params): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->select('t', 'c', 'r')
            ->leftJoin('t.category', 'c')
            ->leftJoin('t.receipts', 'r')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        $orderBy  = in_array($params->orderBy, ['description', 'amount', 'date', 'category'])
            ? $params->orderBy
            : 'date';
        $orderDir = strtolower($params->orderDir) === 'asc' ? 'asc' : 'desc';

        if (! empty($params->searchTerm)) {
            $query->where('t.description LIKE :description')
                  ->setParameter('description', '%' . addcslashes($params->searchTerm, '%_') . '%');
        }

        if ($orderBy === 'category') {
            $query->orderBy('c.name', $orderDir);
        } else {
            $query->orderBy('t.' . $orderBy, $orderDir);
        }

        return new Paginator($query);
    }

    public function getById(int $id): ?Transaction
    {
        return $this->entityManager->find(Transaction::class, $id);
    }

    public function update(Transaction $transaction, TransactionData $transactionData, int $userId): Transaction
    {
        $transaction->setDescription($transactionData->description);
        $transaction->setAmount($transactionData->amount);
        $transaction->setDate($transactionData->date);
        $transaction->setCategory($transactionData->category);
        $this->cache->clear();

        return $transaction;
    }

    public function toggleReviewed(Transaction $transaction): void
    {
        $transaction->setReviewed(! $transaction->wasReviewed());
    }

    public function getTotals(\DateTime $startDate, \DateTime $endDate, int $userId): array
    {
        $cachedKey = "totals_{$userId}";

        if ($this->cache->has($cachedKey)) {
            return $this->cache->get($cachedKey);
        }

        $result =   $this->entityManager->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->select(
                'sum(t.amount) as net, 
                       sum(CASE WHEN t.amount > 0 THEN t.amount ELSE 0 END) as income,
                       sum(CASE WHEN t.amount < 0 THEN t.amount ELSE 0 END) as expense
                ')
            ->where('t.date BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleResult();

        $this->cache->set($cachedKey, $result, 3600);

        return $result;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getRecentTransactions(int $limit, int $userId): array
    {
        $cachedKey = "recent_transactions_{$userId}";

        if ($this->cache->has($cachedKey)) {
            return $this->cache->get($cachedKey);
        }
        $result = $this->entityManager->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->select('t', 'c')
            ->leftjoin('t.category', 'c')
            ->orderBy('t.date', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        $this->cache->set($cachedKey, $result, 3600);

        return $result;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getMonthlySummary(int $year, int $userId): array
    {
        $cachedKey = "monthly_summary_{$userId}";


        if ($this->cache->has($cachedKey)) {
            return $this->cache->get($cachedKey);
        }

        $result =  $this->entityManager->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->select('
                        sum (CASE WHEN t.amount > 0 THEN t.amount ELSE 0 END) as income,
                        sum (CASE WHEN t.amount < 0 THEN abs(t.amount) ELSE 0 END) as expense,
                        MONTH(t.date) as m
            ')
            ->where('YEAR(t.date) = :year')
            ->groupBy('m')
            ->orderBy('m', 'ASC')
            ->setParameter('year', $year)
            ->getQuery()
            ->getArrayResult();

        $this->cache->set($cachedKey, $result, 3600);


        return $result;
    }
}
