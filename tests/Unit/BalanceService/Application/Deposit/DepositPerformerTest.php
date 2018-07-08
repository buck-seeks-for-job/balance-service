<?php

namespace Iqoption\Test\Unit\BalanceService\Application\Deposit;

use Iqoption\BalanceService\Application\Deposit\DepositPerformer;
use Iqoption\BalanceService\Application\Deposit\DepositRequest;
use Iqoption\BalanceService\Application\Exception\AccountNotFoundException;
use Iqoption\BalanceService\Application\Exception\CurrencyMismatchException;
use Iqoption\BalanceService\Application\Exception\NoNominalAccountException;
use Iqoption\BalanceService\Common\Money;
use Iqoption\BalanceService\Domain\Account\Account;
use Iqoption\BalanceService\Domain\Account\NominalAccount;
use Iqoption\BalanceService\Domain\Account\UserAccount;
use Iqoption\BalanceService\Domain\Entry;
use Iqoption\BalanceService\Domain\Transaction;
use Iqoption\BalanceService\Infrastructure\Persistence\DoctrineTransactionManager;
use Iqoption\Test\TestUtility\DoctrineSqliteTestCase;
use PHPUnit\Framework\TestCase;

class DepositPerformerTest extends DoctrineSqliteTestCase
{
    /**
     * @var DepositPerformer
     */
    private $depositPerformer;

    protected function setUp()
    {
        parent::setUp();

        $this->depositPerformer = new DepositPerformer(
            self::$entityManager->getRepository(Account::class),
            self::$entityManager->getRepository(Transaction::class),
            new DoctrineTransactionManager(
                self::$entityManager
            )
        );
    }

    /**
     * @test
     */
    public function deposit_GivenRequestWithUnknownAccountId_ThrowsCertainException()
    {
        $this->givenNominalAccount($ownerId = 'bank', $currency = 'RUB');

        $this->expectException(AccountNotFoundException::class);
        $this->depositPerformer->deposit(new DepositRequest(
            'a89e5c76-2b14-495f-88e3-278003e90936',
            11,
            new Money(1000 * Money::MULTIPLIER, 'RUB')
        ));
    }

    /**
     * @test
     */
    public function deposit_GivenNoNominalAccountWithGivenCurrency_ThrowsCertainException()
    {
        $this->givenUserAccount($name = 'Robin Bobin', $currency = 'RUB');

        $this->expectException(NoNominalAccountException::class);
        $this->depositPerformer->deposit(new DepositRequest(
            'a89e5c76-2b14-495f-88e3-278003e90936',
            1,
            new Money(1000 * Money::MULTIPLIER, 'RUB')
        ));
    }

    /**
     * @test
     */
    public function deposit_GivenCorrectRequest_CreatesTransactionWithCorrectEntries()
    {
        $nominalAccountId = $this->givenNominalAccount($ownerId = 'bank', $currency = 'RUB');
        $accountId = $this->givenUserAccount($name = 'Robin Bobin', $currency);
        $now = new \DateTimeImmutable('now');

        $this->depositPerformer->deposit(new DepositRequest(
            $transactionId = 'a89e5c76-2b14-495f-88e3-278003e90936',
            $accountId,
            $amount = new Money(1000 * Money::MULTIPLIER, 'RUB')
        ));

        $this->assertThatTransactionPersisted($transactionId, [
            'type' => 'deposit',
            'createdAt' => $now,
        ]);
        $this->assertThatAccountHasCertainBalance($accountId, $amount);
        $this->assertThatAccountHasCertainBalance($nominalAccountId, new Money(-1000 * Money::MULTIPLIER, 'RUB'));
    }

    /**
     * @test
     */
    public function deposit_GivenRequestWithIncorrectCurrency_ThrowsCertainException()
    {
        $nominalAccountId = $this->givenNominalAccount($ownerId = 'bank', 'USD');
        $accountId = $this->givenUserAccount($name = 'Robin Bobin', 'RUB');

        $this->expectException(CurrencyMismatchException::class);
        $this->depositPerformer->deposit(new DepositRequest(
            $transactionId = 'a89e5c76-2b14-495f-88e3-278003e90936',
            $accountId,
            $amount = new Money(1000 * Money::MULTIPLIER, 'USD')
        ));
    }

    protected static function getAnnotationMetadataConfigurationPaths(): array
    {
        return [
            self::getClassDirectory(Account::class),
            self::getClassDirectory(Transaction::class),
            self::getClassDirectory(Entry::class)
        ];
    }

    private function givenNominalAccount(string $ownerId, string $currency): int
    {
        $account = new NominalAccount($ownerId, $currency);

        self::$entityManager->persist($account);
        self::$entityManager->flush();

        return (int)$this->getFieldFromAccount($account, 'id');
    }

    private function givenUserAccount(string $name, string $currency)
    {
        $account = new UserAccount($name, $currency);

        self::$entityManager->persist($account);
        self::$entityManager->flush();

        return (int)$this->getFieldFromAccount($account, 'id');
    }

    private function getFieldFromAccount(Account $account, string $name)
    {
        $reflection = new \ReflectionProperty(get_class($account), $name);
        $reflection->setAccessible(true);

        return $reflection->getValue($account);
    }

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