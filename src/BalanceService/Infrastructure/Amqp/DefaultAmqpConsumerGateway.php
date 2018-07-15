<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Infrastructure\Amqp;

use PhpAmqpLib\Connection\AMQPLazyConnection;
use PhpAmqpLib\Message\AMQPMessage;

class DefaultAmqpConsumerGateway implements AmqpConsumerGatewayInterface
{
    /**
     * @var AMQPLazyConnection
     */
    private $amqpConnection;

    public function __construct(AMQPLazyConnection $amqpConnection)
    {
        $this->amqpConnection = $amqpConnection;
    }

    public function consume(callable $onConsume, AmqpConsumerConfig $config): void
    {
        sleep(30); //ugly crutch because RabbitMQ starts later than we try to connect
        $channel = $this->amqpConnection->channel();
        $channel->queue_declare(
            $config->queueName,
            false,
            true,
            false,
            false,
            false
        );

        $channel->exchange_declare($config->exchangeName, 'direct', false, true, false);
        $channel->queue_bind($config->queueName, $config->exchangeName);

        $channel->basic_consume(
            $config->queueName,
            '',
            false,
            false,
            false,
            false,
            function (AMQPMessage $message) use ($onConsume) {
                $onConsume($message->body);
                $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
            }
        );

        try {
            while (count($channel->callbacks)) {
                $channel->wait();
            }
        } finally {
            $channel->close();
            $this->amqpConnection->close();
        }
    }
}
