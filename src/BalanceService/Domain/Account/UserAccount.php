<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Domain\Account;

use Doctrine\ORM\Mapping as ORM;
use Iqoption\BalanceService\Application\Exception\CurrencyMismatchException;
use Iqoption\BalanceService\Common\Money;
use Iqoption\BalanceService\Domain\Transaction;

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
}
