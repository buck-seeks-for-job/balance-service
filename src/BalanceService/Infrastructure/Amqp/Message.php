<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Infrastructure\Amqp;

use Assert\Assert;
use Iqoption\BalanceService\Application\Deposit\DepositRequest;
use Iqoption\BalanceService\Application\Transfer\TransferRequest;
use Iqoption\BalanceService\Application\Withdraw\WithdrawRequest;
use JMS\Serializer\Annotation as JMS;

final class Message
{
    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_WITHDRAW = 'withdraw';
    public const TYPE_TRANSFER = 'transfer';

    /**
     * @var string
     * @JMS\Type("string")
     */
    private $type;

    /**
     * @var DepositRequest|null
     * @JMS\Type("Iqoption\BalanceService\Application\Deposit\DepositRequest")
     */
    private $depositRequest;

    /**
     * @var WithdrawRequest|null
     * @JMS\Type("Iqoption\BalanceService\Application\Withdraw\WithdrawRequest")
     */
    private $withdrawRequest;

    /**
     * @var TransferRequest|null
     * @JMS\Type("Iqoption\BalanceService\Application\Transfer\TransferRequest")
     */
    private $transferRequest;

    public static function deposit(DepositRequest $depositRequest): self
    {
        $me = new self;
        $me->type = self::TYPE_DEPOSIT;
        $me->depositRequest = $depositRequest;

        return $me;
    }

    public static function withdraw(WithdrawRequest $withdrawRequest): self
    {
        $me = new self;
        $me->type = self::TYPE_WITHDRAW;
        $me->withdrawRequest = $withdrawRequest;

        return $me;
    }

    public static function transfer(TransferRequest $transferRequest): self
    {
        $me = new self;
        $me->type = self::TYPE_TRANSFER;
        $me->transferRequest = $transferRequest;

        return $me;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDepositRequest(): DepositRequest
    {
        Assert::that($this->depositRequest)->notNull();

        return $this->depositRequest;
    }

    public function getWihdrawRequest(): WithdrawRequest
    {
        Assert::that($this->withdrawRequest)->notNull();

        return $this->withdrawRequest;
    }

    public function getTransferRequest(): TransferRequest
    {
        Assert::that($this->transferRequest)->notNull();

        return $this->transferRequest;
    }
}
