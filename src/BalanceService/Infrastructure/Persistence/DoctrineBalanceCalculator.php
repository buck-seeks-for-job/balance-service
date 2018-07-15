<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Infrastructure\Persistence;

use Doctrine\ORM\EntityManager;
use Iqoption\BalanceService\Common\Money;
use Iqoption\BalanceService\Domain\Account\Account;
use Iqoption\BalanceService\Domain\BalanceCalculator;
use Iqoption\BalanceService\Domain\Transaction\Entry;

class DoctrineBalanceCalculator implements BalanceCalculator
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function calculate(int $accountId): Money
    {
        $account = $this->entityManager->find(Account::class, $accountId);

        $qb = $this->entityManager->createQueryBuilder();
        $amount = $qb->select('SUM(e.amount.amount)')
            ->from(Entry::class, 'e')
            ->where('e.accountId = :accountId')
            ->setParameter('accountId', $accountId)
            ->getQuery()
            ->getSingleScalarResult();

        return new Money((int)$amount, $account->getCurrency());
    }
}
