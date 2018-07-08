<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Domain\Account;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class NominalAccount extends Account
{

    /**
     * @var string
     *
     * @ORM\Column(name="owner_id", type="string", length=20)
     */
    private $ownerId;

    public function __construct(string $ownerId, string $currency)
    {
        $this->ownerId = $ownerId;
        $this->createdAt = new \DateTime('now');
        $this->currency = $currency;
    }
}
