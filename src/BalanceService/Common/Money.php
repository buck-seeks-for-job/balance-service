<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Common;

use JMS\Serializer\Annotation as JMS;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable()
 */
class Money
{
    public const MULTIPLIER = 1000000;

    /**
     * @var int
     *
     * @ORM\Column(name="amount", type="bigint")
     * @JMS\Type("integer")
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=3)
     * @JMS\Type("string")
     */
    private $currency;

    public function __construct(int $amount, string $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function inverse(): self
    {
        return new self($this->amount * (-1), $this->currency);
    }

    public function greaterThan(Money $amount): bool
    {
        if ($this->currency !== $amount->currency) {
            throw new \InvalidArgumentException('Can not compare different currencies');
        }

        return $this->amount > $amount->amount;
    }
}
