<?php
declare(strict_types=1);

namespace Iqoption\Test\Unit\BalanceService;

use Iqoption\BalanceService\Domain\Transaction;

trait TransactionAwareTestCase
{
    private function assertThatTransactionPersisted(string $id, array $expectedFieldMap): void
    {
        $transaction = self::$entityManager->find(Transaction::class, $id);

        foreach ($expectedFieldMap as $name => $expectedValue) {
            if ($expectedValue instanceof \DateTimeInterface) {
                $actualValue = $this->getFieldFromTransaction($transaction, $name);

                assertThat(abs($actualValue->getTimestamp() - $expectedValue->getTimestamp()), lessThan(10));
            } else {
                assertThat($this->getFieldFromTransaction($transaction, $name), is(equalTo($expectedValue)));
            }
        }
    }

    private function getFieldFromTransaction(Transaction $transaction, string $name)
    {
        $reflection = new \ReflectionProperty(Transaction::class, $name);
        $reflection->setAccessible(true);

        return $reflection->getValue($transaction);
    }
}