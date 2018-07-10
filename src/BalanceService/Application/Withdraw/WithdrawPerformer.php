<?php

namespace Iqoption\BalanceService\Application\Withdraw;

use Iqoption\BalanceService\Application\Exception\AccountNotFoundException;
use Iqoption\BalanceService\Domain\Account\AccountRepository;
use Iqoption\BalanceService\Domain\BalanceCalculator;
use Iqoption\BalanceService\Domain\DatabaseTransactionManager;
use Iqoption\BalanceService\Domain\Event\Event;
use Iqoption\BalanceService\Domain\Event\EventPublisher;
use Iqoption\BalanceService\Domain\Transaction\TransactionRepostory;

class WithdrawPerformer
{
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var TransactionRepostory
     */
    private $transactionRepository;

    /**
     * @var BalanceCalculator
     */
    private $balanceCalculator;

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
        TransactionRepostory $transactionRepository,
        BalanceCalculator $balanceCalculator,
        DatabaseTransactionManager $databaseTransactionManager,
        EventPublisher $eventPublisher
    ) {
        $this->accountRepository = $accountRepository;
        $this->transactionRepository = $transactionRepository;
        $this->balanceCalculator = $balanceCalculator;
        $this->databaseTransactionManager = $databaseTransactionManager;
        $this->eventPublisher = $eventPublisher;
    }

    public function withdraw(WithdrawRequest $withdrawRequest): void
    {
        $this->databaseTransactionManager->transactional(function () use ($withdrawRequest) {
            $userAccount = $this->accountRepository->findAndLockById($withdrawRequest->getAccountId());
            $nominalAccount = $this->accountRepository->findNominalAccountByOwnerAndCurrency(
                'bank',
                $withdrawRequest->getAmount()->getCurrency()
            );

            $transaction = $userAccount->createWithdrawTransaction(
                $withdrawRequest->getTransactionId(),
                $withdrawRequest->getAmount(),
                $nominalAccount,
                $this->balanceCalculator
            );

            $this->transactionRepository->add($transaction);
        });

        $this->eventPublisher->publish(new Event(
                Event::TYPE_WITHDRAW,
                $withdrawRequest->getAccountId(),
                $withdrawRequest->getAmount())
        );
    }
}