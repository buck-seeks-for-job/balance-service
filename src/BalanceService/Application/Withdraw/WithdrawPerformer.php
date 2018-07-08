<?php

namespace Iqoption\BalanceService\Application\Withdraw;

use Iqoption\BalanceService\Application\Exception\AccountNotFoundException;
use Iqoption\BalanceService\Domain\Account\AccountRepository;
use Iqoption\BalanceService\Domain\BalanceCalculator;
use Iqoption\BalanceService\Domain\DatabaseTransactionManager;
use Iqoption\BalanceService\Domain\TransactionRepostory;

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
     * @var DatabaseTransactionManager
     */
    private $databaseTransactionManager;
    /**
     * @var BalanceCalculator
     */
    private $balanceCalculator;

    public function __construct(
        AccountRepository $accountRepository,
        TransactionRepostory $transactionRepository,
        BalanceCalculator $balanceCalculator,
        DatabaseTransactionManager $databaseTransactionManager
    ) {
        $this->accountRepository = $accountRepository;
        $this->transactionRepository = $transactionRepository;
        $this->balanceCalculator = $balanceCalculator;
        $this->databaseTransactionManager = $databaseTransactionManager;
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
    }
}