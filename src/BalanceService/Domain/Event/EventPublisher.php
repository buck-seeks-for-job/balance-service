<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Domain\Event;

interface EventPublisher
{
    public function publish(Event $event): void;
}
