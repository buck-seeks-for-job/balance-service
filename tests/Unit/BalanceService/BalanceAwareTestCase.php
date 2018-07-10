<?php
declare(strict_types=1);

namespace Iqoption\Test\Unit\BalanceService;

use Iqoption\BalanceService\Common\Money;
use Iqoption\BalanceService\Domain\Account\Account;
use Iqoption\BalanceService\Domain\Account\NominalAccount;
use Iqoption\BalanceService\Domain\Transaction\Entry;

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

    private function accountHasCertainBalance(int $accountId, Money $amount): void
    {
        $nominalAccount = new NominalAccount('gateway', $amount->getCurrency());
        self::$entityManager->persist($nominalAccount);
        self::$entityManager->flush();

        $userAccount = self::$entityManager->find(Account::class, $accountId);

        $transaction = $userAccount->createDepositTransaction(
            '458df4a6-2321-4162-969c-34493bfad90f', //todo: generate uuid
            $amount,
            $nominalAccount
        );

        self::$entityManager->persist($transaction);
        self::$entityManager->flush();
    }
}