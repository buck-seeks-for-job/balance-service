<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Infrastructure\Amqp;

use Iqoption\BalanceService\Domain\Event\Event;
use Iqoption\BalanceService\Domain\Event\EventPublisher;
use PhpAmqpLib\Connection\AMQPLazyConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpEventPublisher implements EventPublisher
{
    const QUEUE = 'balance_service.event_queue';
    const EXCHANGE = 'balance_service.event_exchange';
    /**
     * @var AMQPLazyConnection
     */
    private $amqpConnection;

    public function __construct(AMQPLazyConnection $amqpConnection)
    {
        $this->amqpConnection = $amqpConnection;
    }

    public function publish(Event $event): void
    {
        $channel = $this->amqpConnection->channel();
        $channel->queue_declare(self::QUEUE, false, true, false, false);
        $channel->exchange_declare(self::EXCHANGE, 'direct', false, true, false);
        $channel->queue_bind(self::QUEUE, self::EXCHANGE);

        $message = new AMQPMessage(json_encode($event), [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_NON_PERSISTENT
        ]);

        $channel->basic_publish($message, self::EXCHANGE);
    }
}