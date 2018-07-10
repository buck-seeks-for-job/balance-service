<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Domain\Transaction;

use Iqoption\BalanceService\Common\Money;
use Doctrine\ORM\Mapping as ORM;
use Iqoption\BalanceService\Domain\Transaction\Transaction;

/**
 * @ORM\Entity()
 * @ORM\Table(name="entries", indexes={
 *     @ORM\Index(
 *     name="idx_entries_transaction_id",
 *     columns={"transaction_id"})
 * })
 */
class Entry
{
    /**
     * @var string
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="bigint")
     * @ORM\GeneratedValue()
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var Money
     *
     * @ORM\Embedded(class="Iqoption\BalanceService\Common\Money")
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="account_id", type="integer")
     */
    private $accountId;

    /**
     * @var Transaction
     *
     * @ORM\ManyToOne(targetEntity="Iqoption\BalanceService\Domain\Transaction\Transaction", inversedBy="entries")
     * @ORM\JoinColumn(name="transaction_id", referencedColumnName="id")
     */
    private $transaction;

    public function __construct(
        int $accountId,
        Money $amount,
        \DateTimeInterface $createdAt,
        Transaction $transaction
    ) {
        $this->createdAt = $createdAt;
        $this->amount = $amount;
        $this->accountId = $accountId;
        $this->transaction = $transaction;
    }
}