<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Infrastructure\Amqp;

class AmqpConsumerConfig
{
    /**
     * @var string
     */
    public $queueName;

    /**
     * @var string
     */
    public $exchangeName;

    public function __construct(string $queueName, string $exchangeName = '')
    {
        $this->queueName = $queueName;
        $this->exchangeName = $exchangeName;
    }
}