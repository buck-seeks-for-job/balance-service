<?php
declare(strict_types=1);

namespace Iqoption\Test\Stubs;

use Iqoption\BalanceService\Infrastructure\Amqp\AmqpConsumerConfig;
use Iqoption\BalanceService\Infrastructure\Amqp\AmqpConsumerGatewayInterface;

class AmqpConsumerGatewayStub implements AmqpConsumerGatewayInterface
{
    /**
     * @var string[]
     */
    public $messages = [];

    /**
     * @var AmqpConsumerConfig
     */
    public $lastRecordedConfig;

    public function consume(callable $onConsume, AmqpConsumerConfig $config): void
    {
        $this->lastRecordedConfig = $config; //I expect to call this method once

        foreach ($this->messages as $message) {
            $onConsume($message);
        }
    }
}