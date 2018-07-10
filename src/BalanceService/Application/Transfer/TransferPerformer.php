<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Application\Transfer;

use Iqoption\BalanceService\Domain\Account\AccountRepository;
use Iqoption\BalanceService\Domain\BalanceCalculator;
use Iqoption\BalanceService\Domain\DatabaseTransactionManager;
use Iqoption\BalanceService\Domain\Event\Event;
use Iqoption\BalanceService\Domain\Event\EventPublisher;
use Iqoption\BalanceService\Domain\Transaction\TransactionRepostory;

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
    )
    {
        $this->accountRepository = $accountRepository;
        $this->transactionRepository = $transactionRepository;
        $this->balanceCalculator = $balanceCalculator;
        $this->databaseTransactionManager = $databaseTransactionManager;
        $this->eventPublisher = $eventPublisher;
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
        $this->publishEvents($transferRequest);
    }

    private function publishEvents(TransferRequest $transferRequest): void
    {
        $this->eventPublisher->publish(new Event(
                Event::TYPE_WITHDRAW,
                $transferRequest->getFromAccountId(),
                $transferRequest->getAmount())
        );

        $this->eventPublisher->publish(new Event(
                Event::TYPE_DEPOSIT,
                $transferRequest->getToAccountId(),
                $transferRequest->getAmount())
        );
    }
}