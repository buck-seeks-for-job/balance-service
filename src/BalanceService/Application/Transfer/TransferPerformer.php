<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Application\Transfer;

use Iqoption\BalanceService\Domain\Account\AccountRepository;
use Iqoption\BalanceService\Domain\BalanceCalculator;
use Iqoption\BalanceService\Domain\DatabaseTransactionManager;
use Iqoption\BalanceService\Domain\TransactionRepostory;

class TransferPerformer
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

    public function transfer(TransferRequest $transferRequest): void
    {
        $this->databaseTransactionManager->transactional(function () use ($transferRequest) {
            $fromAccount = $this->accountRepository->findAndLockById($transferRequest->getFromAccountId());
            $toAccount = $this->accountRepository->findUserAccountById($transferRequest->getToAccountId());

            $transaction = $fromAccount->createTransferTransaction(
                $transferRequest->getTransactionId(),
                $transferRequest->getAmount(),
                $toAccount,
                $this->balanceCalculator
            );

            $this->transactionRepository->add($transaction);
        });
    }
}