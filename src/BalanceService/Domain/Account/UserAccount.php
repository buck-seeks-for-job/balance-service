<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Domain\Account;

use Doctrine\ORM\Mapping as ORM;
use Iqoption\BalanceService\Application\Exception\CurrencyMismatchException;
use Iqoption\BalanceService\Application\Exception\NotEnoughMoneyException;
use Iqoption\BalanceService\Common\Money;
use Iqoption\BalanceService\Domain\BalanceCalculator;
use Iqoption\BalanceService\Domain\Transaction\Transaction;

/**
 * @ORM\Entity
 */
class UserAccount extends Account
{
    /**
     * @var int
     *
     * @ORM\Column(name="name", type="string", length=32, nullable=true)
     */
    private $name;

    public function __construct(string $name, string $currency)
    {
        $this->createdAt = new \DateTime('now');
        $this->name = $name;
        $this->currency = $currency;
    }

    public function createDepositTransaction(
        string $transactionId,
        Money $amount,
        NominalAccount $nominalAccount
    ): Transaction {
        if ($this->currency !== $amount->getCurrency()) {
            throw new CurrencyMismatchException;
        }

        return Transaction::deposit($transactionId, $nominalAccount->id, $this->id, $amount);
    }

    public function createWithdrawTransaction(
        string $transactionId,
        Money $amount,
        NominalAccount $nominalAccount,
        BalanceCalculator $balanceCalculator
    ): Transaction {
        if ($this->currency !== $amount->getCurrency()) {
            throw new CurrencyMismatchException;
        }

        $balance = $balanceCalculator->calculate($this->id);
        if ($amount->greaterThan($balance)) {
            throw new NotEnoughMoneyException;
        }

        return Transaction::withdraw($transactionId, $this->id, $nominalAccount->id, $amount);
    }

    public function createTransferTransaction(
        string $transactionId,
        Money $amount,
        UserAccount $toAccount,
        BalanceCalculator $balanceCalculator
    ): Transaction {
        if ($this->currency !== $amount->getCurrency()) {
            throw new CurrencyMismatchException;
        }

        if ($toAccount->currency !== $amount->getCurrency()) {
            throw new CurrencyMismatchException;
        }

        $balance = $balanceCalculator->calculate($this->id);
        if ($amount->greaterThan($balance)) {
            throw new NotEnoughMoneyException;
        }

        return Transaction::transfer($transactionId, $this->id, $toAccount->id, $amount);
    }

    public function getId(): int
    {
        return $this->id;
    }
}
