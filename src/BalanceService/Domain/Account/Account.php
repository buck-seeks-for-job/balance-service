<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Domain\Account;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Iqoption\BalanceService\Infrastructure\Persistence\DoctrineAccountRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string", length=10)
 * @ORM\DiscriminatorMap({"nominal" = "NominalAccount", "user" = "UserAccount"})
 * @ORM\Table(name="accounts", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unq_type_currency",
 *     columns={"type", "owner_id", "currency"},
 *     options={"where": "type='nominal'"})
 * })
 */
abstract class Account
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue()
     */
    protected $id;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=3)
     */
    protected $currency;

    public function getCurrency(): string
    {
        return $this->currency;
    }
}
