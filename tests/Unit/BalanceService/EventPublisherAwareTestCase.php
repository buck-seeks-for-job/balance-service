<?php
declare(strict_types=1);

namespace Iqoption\Test\Unit\BalanceService;

use Iqoption\BalanceService\Domain\Event\Event;

trait EventPublisherAwareTestCase
{
    /**
     * @var Event[]
     */
    private $publishedEvents = [];

    public function publish(Event $event): void
    {
        $this->publishedEvents[] = $event;
    }

    private function assertThatCertainEventPublished(Event $event): void
    {
        foreach ($this->publishedEvents as $publishedEvent) {
            if ($publishedEvent == $event) {
                return;
            }
        }

        $this->fail('No expected event published');
    }
}