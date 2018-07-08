<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Application\Withdraw;

use Iqoption\BalanceService\Common\Money;

class WithdrawRequest
{
/**
     * @var string
     */
    private $transactionId;

    /**
     * @var int
     */
    private $accountId;

    /**
     * @var Money
     */
    private $amount;

    public function __construct(string $transactionId, int $accountId, Money $amount)
    {
        $this->transactionId = $transactionId;
        $this->accountId = $accountId;
        $this->amount = $amount;
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }
}