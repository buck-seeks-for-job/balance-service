<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Infrastructure\Presentation\Command;

use Iqoption\BalanceService\Application\Deposit\DepositPerformer;
use Iqoption\BalanceService\Application\Transfer\TransferPerformer;
use Iqoption\BalanceService\Application\Withdraw\WithdrawPerformer;
use Iqoption\BalanceService\Infrastructure\Amqp\AmqpConsumerConfig;
use Iqoption\BalanceService\Infrastructure\Amqp\AmqpConsumerGatewayInterface;
use Iqoption\BalanceService\Infrastructure\Amqp\Message;
use JMS\Serializer\Serializer;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueueListenerCommand extends ContainerAwareCommand
{
    public const QUEUE = 'balance_service.operation_queue';
    public const EXCHANGE = 'balance_service.exchange';

    /**
     * @var DepositPerformer
     */
    private $depositPerformer;

    /**
     * @var WithdrawPerformer
     */
    private $withdrawPerformer;

    /**
     * @var TransferPerformer
     */
    private $transferPerformer;

    /**
     * @var AmqpConsumerGatewayInterface
     */
    private $amqpConsumerGateway;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        DepositPerformer $depositPerformer,
        WithdrawPerformer $withdrawPerformer,
        TransferPerformer $transferPerformer,
        AmqpConsumerGatewayInterface $amqpConsumerGateway,
        Serializer $serializer,
        LoggerInterface $logger
    ) {
        parent::__construct();
        $this->depositPerformer = $depositPerformer;
        $this->withdrawPerformer = $withdrawPerformer;
        $this->transferPerformer = $transferPerformer;
        $this->amqpConsumerGateway = $amqpConsumerGateway;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this->setName('iqoption:queue:listen')->setDescription('Listens to RabbitMQ queue');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->amqpConsumerGateway->consume(function (string $rawMessage) {
            try {
                /** @var Message $message */
                $message = $this->serializer->deserialize($rawMessage, Message::class, 'json');

                $this->processMessage($message);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage(), [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }, new AmqpConsumerConfig(self::QUEUE, self::EXCHANGE));
    }

    private function processMessage(Message $message): void
    {
        switch ($message->getType()) {
            case Message::TYPE_DEPOSIT:
                $this->depositPerformer->deposit($message->getDepositRequest());
                break;
            case Message::TYPE_WITHDRAW:
                $this->withdrawPerformer->withdraw($message->getWihdrawRequest());
                break;
            case Message::TYPE_TRANSFER:
                $this->transferPerformer->transfer($message->getTransferRequest());
                break;
            default:
                $this->logger->error('Unknown message type', ['type' => $message->getType()]);
        }
    }
}
