<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Infrastructure\Amqp;

interface AmqpConsumerGatewayInterface
{
    public function consume(callable $onConsume, AmqpConsumerConfig $config): void;
}
