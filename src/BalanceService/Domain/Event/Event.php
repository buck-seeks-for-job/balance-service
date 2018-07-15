<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Domain\Event;

use Iqoption\BalanceService\Common\Money;

class Event implements \JsonSerializable
{
    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_WITHDRAW = 'withdraw';

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $accountId;

    /**
     * @var Money
     */
    private $amount;

    public function __construct(string $type, int $accountId, Money $amount)
    {
        $this->type = $type;
        $this->accountId = $accountId;
        $this->amount = $amount;
    }

    public function jsonSerialize()
    {
        return [
            'type' => $this->type,
            'accountId' => $this->accountId,
            'amount' => [
                'amount' => $this->amount->getAmount(),
                'currency' => $this->amount->getCurrency()
            ]
        ];
    }
}
