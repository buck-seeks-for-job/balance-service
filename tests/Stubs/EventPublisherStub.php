<?php
declare(strict_types=1);

namespace Iqoption\Test\Stubs;

use Iqoption\BalanceService\Domain\Event\Event;
use Iqoption\BalanceService\Domain\Event\EventPublisher;

class EventPublisherStub implements EventPublisher
{
    /**
     * @var Event[]
     */
    public $publishedEvents = [];

    public function publish(Event $event): void
    {
        $this->publishedEvents[] = $event;
    }
}
