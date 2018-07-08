<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Application\Deposit;

use Iqoption\BalanceService\Domain\Account\AccountRepository;
use Iqoption\BalanceService\Domain\DatabaseTransactionManager;
use Iqoption\BalanceService\Domain\TransactionRepostory;

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

    public function __construct(
        AccountRepository $accountRepository,
        TransactionRepostory $transactionRepostory,
        DatabaseTransactionManager $transactionManager
    ) {
        $this->accountRepository = $accountRepository;
        $this->transactionRepostory = $transactionRepostory;
        $this->databaseTransactionManager = $transactionManager;
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
    }
}