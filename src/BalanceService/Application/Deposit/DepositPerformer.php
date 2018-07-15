<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Application\Deposit;

use Iqoption\BalanceService\Domain\Account\AccountRepository;
use Iqoption\BalanceService\Domain\DatabaseTransactionManager;
use Iqoption\BalanceService\Domain\Event\Event;
use Iqoption\BalanceService\Domain\Event\EventPublisher;
use Iqoption\BalanceService\Domain\Transaction\TransactionRepostory;

class DepositPerformer
{
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var TransactionRepostory
     */
    private $transactionRepostory;

    /**
     * @var DatabaseTransactionManager
     */
    private $databaseTransactionManager;

    /**
     * @var EventPublisher
     */
    private $eventPublisher;

    public function __construct(
        AccountRepository $accountRepository,
        TransactionRepostory $transactionRepostory,
        DatabaseTransactionManager $transactionManager,
        EventPublisher $eventPublisher
    ) {
        $this->accountRepository = $accountRepository;
        $this->transactionRepostory = $transactionRepostory;
        $this->databaseTransactionManager = $transactionManager;
        $this->eventPublisher = $eventPublisher;
    }

    public function deposit(DepositRequest $depositRequest): void
    {
        $userAccount = $this->accountRepository->findUserAccountById($depositRequest->getAccountId());
        $bankAccount = $this->accountRepository
            ->findNominalAccountByOwnerAndCurrency('bank', $depositRequest->getAmount()->getCurrency());

        $transaction = $userAccount->createDepositTransaction(
            $depositRequest->getTransactionId(),
            $depositRequest->getAmount(),
            $bankAccount
        );

        $this->databaseTransactionManager->transactional(function () use ($transaction) {
            $this->transactionRepostory->add($transaction);
        });

        $this->eventPublisher->publish(new Event(
            Event::TYPE_DEPOSIT,
            $depositRequest->getAccountId(),
            $depositRequest->getAmount())
        );
    }
}
