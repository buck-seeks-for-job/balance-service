<?php
declare(strict_types=1);

namespace Iqoption\Test\Unit\BalanceService;

use Iqoption\BalanceService\Common\Money;

trait BalanceAwareTestCase
{
    private function assertThatAccountHasCertainBalance(int $accountId, Money $expectedAmount): void
    {
        $qb = self::$entityManager->createQueryBuilder();
        $actualAmount = $qb->select('SUM(e.amount.amount)')
            ->from(Entry::class, 'e')
            ->where('e.accountId = :accountId')
            ->setParameter('accountId', $accountId)
            ->getQuery()
            ->getSingleScalarResult();

        assertThat($actualAmount, is(equalTo($expectedAmount->getAmount())));
    }
}