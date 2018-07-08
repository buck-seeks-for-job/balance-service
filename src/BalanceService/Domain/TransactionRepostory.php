<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Domain;

interface TransactionRepostory
{
    public function add(Transaction $transaction): void;
}