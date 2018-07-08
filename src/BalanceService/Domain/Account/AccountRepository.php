<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Domain\Account;

interface AccountRepository
{
    public function add(Account $account): void;

    public function findById(int $id): Account;

    public function findNominalAccountByOwnerAndCurrency(string $ownerId, string $currency): NominalAccount;
}

//probably breaks Interface segregation principle. Better solution was to divide it to three interfaces