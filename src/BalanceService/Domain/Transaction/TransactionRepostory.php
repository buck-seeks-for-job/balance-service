<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Domain\Transaction;

interface TransactionRepostory
{
    public function add(Transaction $transaction): void;
}
