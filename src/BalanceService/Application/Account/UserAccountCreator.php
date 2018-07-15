<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Application\Account;

use Iqoption\BalanceService\Domain\Account\AccountRepository;
use Iqoption\BalanceService\Domain\Account\UserAccount;
use Iqoption\BalanceService\Domain\DatabaseTransactionManager;

class UserAccountCreator
{
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var DatabaseTransactionManager
     */
    private $transactionManager;

    public function __construct(AccountRepository $accountRepository, DatabaseTransactionManager $transactionManager)
    {
        $this->accountRepository = $accountRepository;
        $this->transactionManager = $transactionManager;
    }

    public function create(string $name, string $currency): int
    {
        $userAccount = new UserAccount($name, $currency);

        $this->transactionManager->transactional(function () use ($userAccount) {
            $this->accountRepository->add($userAccount);
        });

        return $userAccount->getId();
    }
}
