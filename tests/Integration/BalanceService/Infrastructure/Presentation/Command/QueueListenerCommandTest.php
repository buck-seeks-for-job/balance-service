<?php
declare(strict_types=1);

namespace Iqoption\Test\Integration\BalanceService\Infrastructure\Presentation\Command;

use Iqoption\BalanceService\Application\Deposit\DepositPerformer;
use Iqoption\BalanceService\Application\Deposit\DepositRequest;
use Iqoption\BalanceService\Common\Money;
use Iqoption\BalanceService\Domain\Account\NominalAccount;
use Iqoption\BalanceService\Domain\Account\UserAccount;
use Iqoption\BalanceService\Domain\Transaction\Transaction;
use Iqoption\BalanceService\Infrastructure\Amqp\Message;
use Iqoption\Test\TestUtility\SymfonyDbTestCase;
use Iqoption\Test\TestUtility\TestOutput;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;

class QueueListenerCommandTest extends SymfonyDbTestCase
{
    /**
     * @test
     */
    public function execute_GivenDepositMessage_DepositsMoney()
    {
        $this->givenNominalAccount($currency = 'RUB');
        $accountId = $this->givenUserAccount('Robin Bobin', $currency);
        $this->givenDepositMessage(
            $transactionId = 'a89e5c76-2b14-495f-88e3-278003e90936',
            $accountId,
            $amount = new Money(1000 * Money::MULTIPLIER, $currency)
        );

        $this->runCommand('iqoption:queue:listen');

        $this->assertThatTransactionCreated($transactionId);
    }

    /**
     * @test
     */
    public function execute_GivenWithdrawMessage_WihdrawsMoney()
    {
        $this->givenNominalAccount($currency = 'RUB');
        $accountId = $this->givenUserAccount('Robin Bobin', $currency);
        $this->certainAmountDeposited($accountId, new Money(1000 * Money::MULTIPLIER, $currency));
        $this->givenWithdrawMessage(
            $transactionId = 'a89e5c76-2b14-495f-88e3-278003e90936',
            $accountId,
            $amount = new Money(1000 * Money::MULTIPLIER, $currency)
        );

        $this->runCommand('iqoption:queue:listen');

        $this->assertThatTransactionCreated($transactionId);
    }

    /**
     * @test
     */
    public function execute_GivenTransferMessage_TransfersMoney()
    {
        $this->givenNominalAccount($currency = 'RUB');
        $accountId1 = $this->givenUserAccount('Robin Bobin', $currency);
        $accountId2 = $this->givenUserAccount('Karabas Barabas', $currency);
        $this->certainAmountDeposited($accountId1, new Money(1000 * Money::MULTIPLIER, $currency));
        $this->givenTransferMessage(
            $transactionId = 'a89e5c76-2b14-495f-88e3-278003e90936',
            $accountId1,
            $accountId2,
            $amount = new Money(1000 * Money::MULTIPLIER, $currency)
        );

        $this->runCommand('iqoption:queue:listen');

        $this->assertThatTransactionCreated($transactionId);
    }

    /**
     * @test
     */
    public function execute_WhenOperationFails_LogsError()
    {
        $this->givenNominalAccount($currency = 'RUB');
        $accountId = $this->givenUserAccount('Robin Bobin', $currency);
        $this->givenWithdrawMessage(
            $transactionId = 'a89e5c76-2b14-495f-88e3-278003e90936',
            $accountId,
            $amount = new Money(1000 * Money::MULTIPLIER, $currency)
        );

        $this->runCommand('iqoption:queue:listen');

        $this->assertThatExceptionIsLogged();
    }

    /**
     * @test
     */
    public function execute_GivenMessageOfUnknownType_LogsError()
    {
        $this->givenMessageOfUnknownType();

        $this->runCommand('iqoption:queue:listen');

        $this->assertThatExceptionIsLogged();
    }


    private function runCommand(string $name): string
    {
        //todo: in future can be moved to certain class or trait
        $application = new Application($this->client->getKernel());
        $application->setAutoExit(false);

        $input = new StringInput($name);
        $output = new TestOutput();

        $application->run($input, $output);

        return $output->output;
    }

    private function givenNominalAccount(string $currency): void
    {
        $account = new NominalAccount('bank', $currency);

        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $em->persist($account);
        $em->flush();
    }

    private function givenUserAccount(string $name, string $currency): int
    {
        $account = new UserAccount($name, $currency);

        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $em->persist($account);
        $em->flush();

        return (int)$account->getId();
    }

    private function givenDepositMessage(string $transactionId, int $accountId, Money $amount): void
    {
        $message = [
            'type' => Message::TYPE_DEPOSIT,
            'depositRequest' => [
                'transactionId' =>  $transactionId,
                'accountId' => $accountId,
                'amount' => [
                    'amount' => $amount->getAmount(),
                    'currency' => $amount->getCurrency()
                ]
            ]
        ];

        $consumerGateway = $this->client->getContainer()->get('iqoption.default_amqp_consumer_gateway');
        $consumerGateway->messages[] = json_encode($message);
    }

    private function assertThatTransactionCreated(string $transactionId): void {
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $transaction = $em->find(Transaction::class, $transactionId);

        assertThat($transaction, is(notNullValue()));
    }

    private function certainAmountDeposited(int $accountId, Money $amount): void
    {
        $depositPerformer = $this->client->getContainer()->get(DepositPerformer::class);

        $depositPerformer->deposit(new DepositRequest(Uuid::uuid4()->toString(), $accountId, $amount));
    }

    private function givenWithdrawMessage(string $transactionId, int $accountId, Money $amount): void
    {
        $message = [
            'type' => Message::TYPE_WITHDRAW,
            'withdrawRequest' => [
                'transactionId' =>  $transactionId,
                'accountId' => $accountId,
                'amount' => [
                    'amount' => $amount->getAmount(),
                    'currency' => $amount->getCurrency()
                ]
            ]
        ];

        $consumerGateway = $this->client->getContainer()->get('iqoption.default_amqp_consumer_gateway');
        $consumerGateway->messages[] = json_encode($message);
    }

    private function givenTransferMessage(
        string $transactionId,
        int $fromAccountId,
        int $toAccountId,
        Money $amount
    ): void {
        $message = [
            'type' => Message::TYPE_TRANSFER,
            'transferRequest' => [
                'transactionId' =>  $transactionId,
                'fromAccountId' => $fromAccountId,
                'toAccountId' => $toAccountId,
                'amount' => [
                    'amount' => $amount->getAmount(),
                    'currency' => $amount->getCurrency()
                ]
            ]
        ];

        $consumerGateway = $this->client->getContainer()->get('iqoption.default_amqp_consumer_gateway');
        $consumerGateway->messages[] = json_encode($message);
    }

    private function assertThatExceptionIsLogged(): void
    {
        $logHandler = $this->client->getContainer()->get('iqoption.queue_listener_command.logger_test_handler');

        //todo: better check logging
        assertThat($logHandler->getRecords(), is(nonEmptyArray()));
    }

    private function givenMessageOfUnknownType()
    {
        $message = [
            'type' => 'unknown'
        ];

        $consumerGateway = $this->client->getContainer()->get('iqoption.default_amqp_consumer_gateway');
        $consumerGateway->messages[] = json_encode($message);
    }
}
