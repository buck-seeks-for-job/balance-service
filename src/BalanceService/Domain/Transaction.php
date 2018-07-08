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
        $me = new self;
        $me->type = self::TYPE_DEPOSIT;
        $me->id = $id;
        $me->createdAt = new \DateTimeImmutable('now');
        $me->entries[] = new Entry($fromAccountId, $amount->inverse(), $me->createdAt, $me);
        $me->entries[] = new Entry($toAccountId, $amount, $me->createdAt, $me);

        return $me;
    }

    public static function withdraw(string $id, int $fromAccountId, int $toAccountId, Money $amount): self
    {
        $me = new self;
        $me->type = self::TYPE_WITHDRAW;
        $me->id = $id;
        $me->createdAt = new \DateTimeImmutable('now');
        $me->entries[] = new Entry($fromAccountId, $amount->inverse(), $me->createdAt, $me);
        $me->entries[] = new Entry($toAccountId, $amount, $me->createdAt, $me);

        return $me;
    }
}