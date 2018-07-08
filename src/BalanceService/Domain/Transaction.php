<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Domain;

use Doctrine\ORM\Mapping as ORM;
use Iqoption\BalanceService\Common\Money;

/**
 * @ORM\Entity(repositoryClass="Iqoption\BalanceService\Infrastructure\Persistence\DoctrineTransactionRepository")
 * @ORM\Table(name="transactions")
 */
class Transaction
{
    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_WITHDRAW = 'withdraw';
    public const TYPE_TRANSFER = 'transfer';

    /**
     * @var string
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="guid")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=16)
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var Entry[]
     *
     * @ORM\OneToMany(targetEntity="Iqoption\BalanceService\Domain\Entry", mappedBy="transaction", cascade={"ALL"})
     */
    private $entries = [];

    public static function deposit(string $id, int $fromAccountId, int $toAccountId, Money $amount): self
    {
        return new self($id, self::TYPE_DEPOSIT, $fromAccountId, $toAccountId, $amount);
    }

    public static function withdraw(string $id, int $fromAccountId, int $toAccountId, Money $amount): self
    {
        return new self($id, self::TYPE_WITHDRAW, $fromAccountId, $toAccountId, $amount);
    }

    public static function transfer(string $id, int $fromAccountId, int $toAccountId, Money $amount): self
    {
        return new self($id, self::TYPE_TRANSFER, $fromAccountId, $toAccountId, $amount);
    }

    private function __construct(string $id, string $type, int $fromAccountId, int $toAccountId, Money $amount)
    {
        $this->id = $id;
        $this->type = $type;
        $this->createdAt = new \DateTimeImmutable('now');
        $this->entries[] = new Entry($fromAccountId, $amount->inverse(), $this->createdAt, $this);
        $this->entries[] = new Entry($toAccountId, $amount, $this->createdAt, $this);
    }
}