<?php
declare(strict_types=1);

namespace Iqoption\Test\Unit\BalanceService\Application\Transfer;

use Iqoption\BalanceService\Application\Exception\AccountNotFoundException;
use Iqoption\BalanceService\Application\Exception\CurrencyMismatchException;
use Iqoption\BalanceService\Application\Exception\NotEnoughMoneyException;
use Iqoption\BalanceService\Application\Transfer\TransferPerformer;
use Iqoption\BalanceService\Application\Transfer\TransferRequest;
use Iqoption\BalanceService\Common\Money;
use Iqoption\BalanceService\Domain\Account\Account;
use Iqoption\BalanceService\Domain\Event\Event;
use Iqoption\BalanceService\Domain\Event\EventPublisher;
use Iqoption\BalanceService\Domain\Transaction\Entry;
use Iqoption\BalanceService\Domain\Transaction\Transaction;
use Iqoption\BalanceService\Infrastructure\Persistence\DoctrineBalanceCalculator;
use Iqoption\BalanceService\Infrastructure\Persistence\DoctrineTransactionManager;
use Iqoption\Test\TestUtility\DoctrineSqliteTestCase;
use Iqoption\Test\Unit\BalanceService\AccountAwareTestCase;
use Iqoption\Test\Unit\BalanceService\BalanceAwareTestCase;
use Iqoption\Test\Unit\BalanceService\EventPublisherAwareTestCase;
use Iqoption\Test\Unit\BalanceService\TransactionAwareTestCase;

class TransferPerformerTest extends DoctrineSqliteTestCase implements EventPublisher
{
    use AccountAwareTestCase;
    use TransactionAwareTestCase;
    use BalanceAwareTestCase;
    use EventPublisherAwareTestCase;

    /**
     * @var TransferPerformer
     */
    private $transferPerformer;

    protected function setUp()
    {
        parent::setUp();

        $this->transferPerformer = new TransferPerformer(
            self::$entityManager->getRepository(Account::class),
            self::$entityManager->getRepository(Transaction::class),
            new DoctrineBalanceCalculator(self::$entityManager),
            new DoctrineTransactionManager(
                self::$entityManager
            ),
            $this
        );
    }

    /**
     * @test
     */
    public function transfer_GivenRequestWithUnknownFromAccountId_ThrowsCertainException()
    {
        $toAcountId = $this->givenUserAccount($name = 'Robin Bobin', $currency = 'RUB');

        $this->expectException(AccountNotFoundException::class);
        $this->transferPerformer->transfer(new TransferRequest(
            'a89e5c76-2b14-495f-88e3-278003e90936',
            11,
            $toAcountId,
            new Money(1000 * Money::MULTIPLIER, 'RUB')
        ));
    }

    /**
     * @test
     */
    public function transfer_GivenRequestWithUnknownToAccountId_ThrowsCertainException()
    {
        $fromAccountId = $this->givenUserAccount($name = 'Robin Bobin', $currency = 'RUB');

        $this->expectException(AccountNotFoundException::class);
        $this->transferPerformer->transfer(new TransferRequest(
            'a89e5c76-2b14-495f-88e3-278003e90936',
            $fromAccountId,
            11,
            new Money(1000 * Money::MULTIPLIER, 'RUB')
        ));
    }

    /**
     * @test
     */
    public function transfer_GivenCorrectRequestAndFromAccountHasEnoughMoneyOnIt_CreatesTransactionWithCorrectEntries()
    {
        $fromAccountId = $this->givenUserAccount($name = 'Robin Bobin', $currency = 'RUB');
        $toAccountId = $this->givenUserAccount($name = 'Karabas Barabas', $currency);
        $this->accountHasCertainBalance($fromAccountId, $amount = new Money(1000 * Money::MULTIPLIER, 'RUB'));
        $now = new \DateTimeImmutable('now');

        $this->transferPerformer->transfer(new TransferRequest(
            $transactionId = 'a89e5c76-2b14-495f-88e3-278003e90936',
            $fromAccountId,
            $toAccountId,
            new Money(1000 * Money::MULTIPLIER, 'RUB')
        ));

        $this->assertThatTransactionPersisted($transactionId, [
            'type' => Transaction::TYPE_TRANSFER,
            'createdAt' => $now,
            'amount' => $amount
        ]);
        $this->assertThatAccountHasCertainBalance($fromAccountId, new Money(0, 'RUB'));
        $this->assertThatAccountHasCertainBalance($toAccountId, $amount);
        $this->assertThatCertainEventPublished(new Event(Event::TYPE_WITHDRAW, $fromAccountId, $amount));
        $this->assertThatCertainEventPublished(new Event(Event::TYPE_DEPOSIT, $toAccountId, $amount));
    }

    /**
     * @test
     */
    public function transfer_WhenFromAccountCurrencyDiffersWithRequested_ThrowsCertainException()
    {
        $fromAccountId = $this->givenUserAccount($name = 'Robin Bobin',  'RUB');
        $toAccountId = $this->givenUserAccount($name = 'Karabas Barabas', 'USD');
        $this->accountHasCertainBalance($fromAccountId, $amount = new Money(1000 * Money::MULTIPLIER, 'RUB'));

        $this->expectException(CurrencyMismatchException::class);
        $this->transferPerformer->transfer(new TransferRequest(
            $transactionId = 'a89e5c76-2b14-495f-88e3-278003e90936',
            $fromAccountId,
            $toAccountId,
            new Money(1000 * Money::MULTIPLIER, 'USD')
        ));
    }

    /**
     * @test
     */
    public function transfer_WhenToAccountCurrencyDiffersWithRequested_ThrowsCertainException()
    {
        $fromAccountId = $this->givenUserAccount($name = 'Robin Bobin',  'RUB');
        $toAccountId = $this->givenUserAccount($name = 'Karabas Barabas', 'USD');
        $this->accountHasCertainBalance($fromAccountId, $amount = new Money(1000 * Money::MULTIPLIER, 'RUB'));

        $this->expectException(CurrencyMismatchException::class);
        $this->transferPerformer->transfer(new TransferRequest(
            $transactionId = 'a89e5c76-2b14-495f-88e3-278003e90936',
            $fromAccountId,
            $toAccountId,
            new Money(1000 * Money::MULTIPLIER, 'RUB')
        ));
    }

    /**
     * @test
     */
    public function transfer_GivenCorrectRequestAndAccountHasNotEnoughMoneyOnIt_ThrowsCertainException()
    {
        $fromAccountId = $this->givenUserAccount($name = 'Robin Bobin', $currency = 'RUB');
        $toAccountId = $this->givenUserAccount($name = 'Karabas Barabas', $currency);
        $this->accountHasCertainBalance($fromAccountId, $amount = new Money(999 * Money::MULTIPLIER, 'RUB'));

        $this->expectException(NotEnoughMoneyException::class);
        $this->transferPerformer->transfer(new TransferRequest(
            $transactionId = 'a89e5c76-2b14-495f-88e3-278003e90936',
            $fromAccountId,
            $toAccountId,
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
