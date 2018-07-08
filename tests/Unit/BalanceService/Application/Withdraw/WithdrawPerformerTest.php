<?php
declare(strict_types=1);

namespace Iqoption\Test\Unit\BalanceService\Application\Withdraw;

use Iqoption\BalanceService\Application\Exception\AccountNotFoundException;
use Iqoption\BalanceService\Application\Exception\CurrencyMismatchException;
use Iqoption\BalanceService\Application\Exception\NoNominalAccountException;
use Iqoption\BalanceService\Application\Exception\NotEnoughMoneyException;
use Iqoption\BalanceService\Application\Withdraw\WithdrawPerformer;
use Iqoption\BalanceService\Application\Withdraw\WithdrawRequest;
use Iqoption\BalanceService\Common\Money;
use Iqoption\BalanceService\Domain\Account\Account;
use Iqoption\BalanceService\Domain\Entry;
use Iqoption\BalanceService\Domain\Transaction;
use Iqoption\BalanceService\Infrastructure\Persistence\DoctrineBalanceCalculator;
use Iqoption\BalanceService\Infrastructure\Persistence\DoctrineTransactionManager;
use Iqoption\Test\TestUtility\DoctrineSqliteTestCase;
use Iqoption\Test\Unit\BalanceService\AccountAwareTestCase;
use Iqoption\Test\Unit\BalanceService\BalanceAwareTestCase;
use Iqoption\Test\Unit\BalanceService\TransactionAwareTestCase;

class WithdrawPerformerTest extends DoctrineSqliteTestCase
{
    use AccountAwareTestCase;
    use TransactionAwareTestCase;
    use BalanceAwareTestCase;

    /**
     * @var WithdrawPerformer
     */
    private $withdrawPerformer;

    protected function setUp()
    {
        parent::setUp();

        $this->withdrawPerformer = new WithdrawPerformer(
            self::$entityManager->getRepository(Account::class),
            self::$entityManager->getRepository(Transaction::class),
            new DoctrineBalanceCalculator(self::$entityManager),
            new DoctrineTransactionManager(
                self::$entityManager
            )
        );
    }

    /**
     * @test
     */
    public function withdraw_GivenRequestWithUnknownAccountId_ThrowsCertainException()
    {
        $this->givenNominalAccount($ownerId = 'bank', $currency = 'RUB');

        $this->expectException(AccountNotFoundException::class);
        $this->withdrawPerformer->withdraw(new WithdrawRequest(
            'a89e5c76-2b14-495f-88e3-278003e90936',
            11,
            new Money(1000 * Money::MULTIPLIER, 'RUB')
        ));
    }

    /**
     * @test
     */
    public function withdraw_GivenNoNominalAccountWithGivenCurrency_ThrowsCertainException()
    {
        $userAccountId = $this->givenUserAccount($name = 'Robin Bobin', $currency = 'RUB');

        $this->expectException(NoNominalAccountException::class);
        $this->withdrawPerformer->withdraw(new WithdrawRequest(
            'a89e5c76-2b14-495f-88e3-278003e90936',
            $userAccountId,
            new Money(1000 * Money::MULTIPLIER, 'RUB')
        ));
    }

    /**
     * @test
     */
    public function withdraw_GivenCorrectRequestAndAccountHasEnoughMoneyOnIt_CreatesTransactionWithCorrectEntries()
    {
        $this->givenNominalAccount($ownerId = 'bank', $currency = 'RUB');
        $accountId = $this->givenUserAccount($name = 'Robin Bobin', $currency);
        $this->accountHasCertainBalance($accountId, $amount = new Money(1000 * Money::MULTIPLIER, 'RUB'));
        $now = new \DateTimeImmutable('now');

        $this->withdrawPerformer->withdraw(new WithdrawRequest(
            $transactionId = 'a89e5c76-2b14-495f-88e3-278003e90936',
            $accountId,
            $amount
        ));

        $this->assertThatTransactionPersisted($transactionId, [
            'type' => Transaction::TYPE_WITHDRAW,
            'createdAt' => $now,
        ]);
        $this->assertThatAccountHasCertainBalance($accountId, new Money(0, 'RUB'));
    }

    /**
     * @test
     */
    public function withdraw_GivenRequestWithIncorrectCurrency_ThrowsCertainException()
    {
        $this->givenNominalAccount($ownerId = 'bank','USD');
        $accountId = $this->givenUserAccount($name = 'Robin Bobin', 'RUB');
        $this->accountHasCertainBalance($accountId, $amount = new Money(1000 * Money::MULTIPLIER, 'RUB'));

        $this->expectException(CurrencyMismatchException::class);
        $this->withdrawPerformer->withdraw(new WithdrawRequest(
            $transactionId = 'a89e5c76-2b14-495f-88e3-278003e90936',
            $accountId,
            new Money(1 * Money::MULTIPLIER, 'USD')
        ));
    }

    /**
     * @test
     */
    public function withdraw_GivenCorrectRequestAndAccountHasNotEnoughMoneyOnIt_ThrowsCertainException()
    {
        $this->givenNominalAccount($ownerId = 'bank', $currency = 'RUB');
        $accountId = $this->givenUserAccount($name = 'Robin Bobin', $currency);
        $this->accountHasCertainBalance($accountId, $amount = new Money(999 * Money::MULTIPLIER, 'RUB'));

        $this->expectException(NotEnoughMoneyException::class);
        $this->withdrawPerformer->withdraw(new WithdrawRequest(
            $transactionId = 'a89e5c76-2b14-495f-88e3-278003e90936',
            $accountId,
            new Money(1000 * Money::MULTIPLIER, 'RUB')
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
}