<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Infrastructure\Amqp;

use Iqoption\BalanceService\Domain\Event\Event;
use Iqoption\BalanceService\Domain\Event\EventPublisher;

class AmqpEventPublisher implements EventPublisher
{
    public function publish(Event $event): void
    {
        // TODO: Implement publish() method.
    }
}