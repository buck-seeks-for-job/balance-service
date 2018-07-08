<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Domain\Account;

interface AccountRepository
{
    public function add(Account $account): void;

    public function findUserAccountById(int $id): UserAccount;

    public function findNominalAccountByOwnerAndCurrency(string $ownerId, string $currency): NominalAccount;

    public function findAndLockById(int $id): UserAccount;
}

//probably breaks Interface segregation principle. Better solution was to divide it to three interfaces