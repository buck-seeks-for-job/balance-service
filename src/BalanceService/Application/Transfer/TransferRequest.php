<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Application\Transfer;

use Iqoption\BalanceService\Common\Money;
use JMS\Serializer\Annotation as JMS;

class TransferRequest
{
    /**
     * @var string
     * @JMS\Type("string")
     */
    private $transactionId;

    /**
     * @var int
     * @JMS\Type("integer")
     */
    private $fromAccountId;

    /**
     * @var int
     * @JMS\Type("integer")
     */
    private $toAccountId;

    /**
     * @var Money
     * @JMS\Type("Iqoption\BalanceService\Common\Money")
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