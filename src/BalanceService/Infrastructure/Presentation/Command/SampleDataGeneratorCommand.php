<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Infrastructure\Presentation\Command;

use Iqoption\BalanceService\Application\Account\UserAccountCreator;
use Iqoption\BalanceService\Application\Deposit\DepositRequest;
use Iqoption\BalanceService\Application\Transfer\TransferRequest;
use Iqoption\BalanceService\Application\Withdraw\WithdrawRequest;
use Iqoption\BalanceService\Common\Money;
use Iqoption\BalanceService\Infrastructure\Amqp\Message;
use JMS\Serializer\Serializer;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPLazyConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SampleDataGeneratorCommand extends ContainerAwareCommand
{
    /**
     * @var UserAccountCreator
     */
    private $userAccountCreator;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var AMQPLazyConnection
     */
    private $amqpConnection;

    public function __construct(UserAccountCreator $userAccountCreator, Serializer $serializer, AMQPLazyConnection $amqpConnection)
    {
        parent::__construct();
        $this->userAccountCreator = $userAccountCreator;
        $this->serializer = $serializer;
        $this->amqpConnection = $amqpConnection;
    }

    protected function configure()
    {
        $this->setName('iqoption:queue:fill')->setDescription('Fills RabbitMQ queue with test data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $accountId1 = $this->userAccountCreator->create('Robin Bobin', 'RUB');
        $accountId2 = $this->userAccountCreator->create('Karabas Barabas', 'RUB');

        $channel = $this->createChannelWithQueue();

        $this->deposit($accountId1, new Money(10000 * Money::MULTIPLIER, 'RUB'), $channel);
        $this->transfer($accountId1, $accountId2, new Money(1000 * Money::MULTIPLIER, 'RUB'), $channel);

        for ($i = 0; $i < 9; $i++) {
            $this->withdraw($accountId1, new Money(1000 * Money::MULTIPLIER, 'RUB'), $channel);
        }

        //these one should not pass
        $this->withdraw($accountId1, new Money(500 * Money::MULTIPLIER, 'RUB'), $channel);
        $this->withdraw($accountId1, new Money(500 * Money::MULTIPLIER, 'RUB'), $channel);
    }

    private function createChannelWithQueue(): AMQPChannel
    {
        $channel = $this->amqpConnection->channel();
        $channel->queue_declare(QueueListenerCommand::QUEUE, false, true, false, false);
        $channel->exchange_declare(QueueListenerCommand::EXCHANGE, 'direct', false, true, false);
        $channel->queue_bind(QueueListenerCommand::QUEUE, QueueListenerCommand::EXCHANGE);

        return $channel;
    }

    private function deposit(int $accountId, Money $amount, AMQPChannel $channel): void
    {
        $depositMessage = Message::deposit(new DepositRequest(
            Uuid::uuid4()->toString(),
            $accountId,
            $amount
        ));

        $message = new AMQPMessage($this->serializer->serialize($depositMessage, 'json'), [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_NON_PERSISTENT
        ]);

        $channel->basic_publish($message, QueueListenerCommand::EXCHANGE);
    }

    private function transfer(int $fromAccountId, int $toAccountId, Money $amount, AMQPChannel $channel): void
    {
        $transferMessage = Message::transfer(new TransferRequest(
            Uuid::uuid4()->toString(),
            $fromAccountId,
            $toAccountId,
            $amount
        ));

        $message = new AMQPMessage($this->serializer->serialize($transferMessage, 'json'), [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_NON_PERSISTENT
        ]);

        $channel->basic_publish($message, QueueListenerCommand::EXCHANGE);
    }

    private function withdraw(int $accountId, Money $amount, AMQPChannel $channel): void
    {
        $withdrawMessage = Message::withdraw(new WithdrawRequest(
            Uuid::uuid4()->toString(),
            $accountId,
            $amount
        ));

        $message = new AMQPMessage($this->serializer->serialize($withdrawMessage, 'json'), [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_NON_PERSISTENT
        ]);

        $channel->basic_publish($message, QueueListenerCommand::EXCHANGE);
    }
}
