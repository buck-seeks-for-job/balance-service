<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Application\Transfer;

use Iqoption\BalanceService\Common\Money;

class TransferRequest
{
    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var int
     */
    private $fromAccountId;

    /**
     * @var int
     */
    private $toAccountId;

    /**
     * @var Money
     */
    private $amount;

    public function __construct(string $transactionId, int $fromAccountId, int $toAccountId, Money $amount)
    {
        $this->transactionId = $transactionId;
        $this->amount = $amount;
        $this->fromAccountId = $fromAccountId;
        $this->toAccountId = $toAccountId;
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getFromAccountId(): int
    {
        return $this->fromAccountId;
    }

    public function getToAccountId(): int
    {
        return $this->toAccountId;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }
}