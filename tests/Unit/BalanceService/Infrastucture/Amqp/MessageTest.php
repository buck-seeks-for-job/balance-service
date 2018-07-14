<?php
declare(strict_types=1);

namespace Iqoption\Test\Unit\BalanceService\Infrastucture\Amqp;

use Iqoption\BalanceService\Application\Deposit\DepositRequest;
use Iqoption\BalanceService\Application\Transfer\TransferRequest;
use Iqoption\BalanceService\Application\Withdraw\WithdrawRequest;
use Iqoption\BalanceService\Common\Money;
use Iqoption\BalanceService\Infrastructure\Amqp\Message;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    /**
     * @var Serializer
     */
    private $serializer;

    protected function setUp()
    {
        $this->serializer = SerializerBuilder::create()->setPropertyNamingStrategy(
            new SerializedNameAnnotationStrategy(new IdenticalPropertyNamingStrategy())
        )->build();
    }

    /**
     * @test
     */
    public function deserialize_GivenDepositMessage_DeserializesItCorrectly()
    {
        $rawMessage = [
            'type' => Message::TYPE_DEPOSIT,
            'depositRequest' => [
                'transactionId' =>  $transactionId = 'a89e5c76-2b14-495f-88e3-278003e90936',
                'accountId' => $accountId = 1,
                'amount' => [
                    'amount' => $amount = 1000 * Money::MULTIPLIER,
                    'currency' => $currency = 'RUB'
                ]
            ]
        ];

        /** @var Message $message */
        $message = $this->serializer->deserialize(json_encode($rawMessage), Message::class, 'json');

        assertThat($message->getType(), is(equalTo(Message::TYPE_DEPOSIT)));
        assertThat($message->getDepositRequest()->getTransactionId(), is(equalTo($transactionId)));
        assertThat($message->getDepositRequest()->getAccountId(), is(equalTo($accountId)));
        assertThat($message->getDepositRequest()->getAmount()->getAmount(), is(equalTo($amount)));
        assertThat($message->getDepositRequest()->getAmount()->getCurrency(), is(equalTo($currency)));
    }

    /**
     * @test
     */
    public function deserialize_GivenWithdrawMessage_DeserializesItCorrectly()
    {
        $rawMessage = [
            'type' => Message::TYPE_WITHDRAW,
            'withdrawRequest' => [
                'transactionId' =>  $transactionId = 'a89e5c76-2b14-495f-88e3-278003e90936',
                'accountId' => $accountId = 1,
                'amount' => [
                    'amount' => $amount = 1000 * Money::MULTIPLIER,
                    'currency' => $currency = 'RUB'
                ]
            ]
        ];

        /** @var Message $message */
        $message = $this->serializer->deserialize(json_encode($rawMessage), Message::class, 'json');

        assertThat($message->getType(), is(equalTo(Message::TYPE_WITHDRAW)));
        assertThat($message->getWihdrawRequest()->getTransactionId(), is(equalTo($transactionId)));
        assertThat($message->getWihdrawRequest()->getAccountId(), is(equalTo($accountId)));
        assertThat($message->getWihdrawRequest()->getAmount()->getAmount(), is(equalTo($amount)));
        assertThat($message->getWihdrawRequest()->getAmount()->getCurrency(), is(equalTo($currency)));
    }

    /**
     * @test
     */
    public function deserialize_GivenTransferMessage_DeserializesItCorrectly()
    {
        $rawMessage = [
            'type' => Message::TYPE_TRANSFER,
            'transferRequest' => [
                'transactionId' =>  $transactionId = 'a89e5c76-2b14-495f-88e3-278003e90936',
                'fromAccountId' => $fromAccountId = 1,
                'toAccountId' => $toAccountId = 2,
                'amount' => [
                    'amount' => $amount = 1000 * Money::MULTIPLIER,
                    'currency' => $currency = 'RUB'
                ]
            ]
        ];

        /** @var Message $message */
        $message = $this->serializer->deserialize(json_encode($rawMessage), Message::class, 'json');

        assertThat($message->getType(), is(equalTo(Message::TYPE_TRANSFER)));
        assertThat($message->getTransferRequest()->getTransactionId(), is(equalTo($transactionId)));
        assertThat($message->getTransferRequest()->getFromAccountId(), is(equalTo($fromAccountId)));
        assertThat($message->getTransferRequest()->getToAccountId(), is(equalTo($toAccountId)));
        assertThat($message->getTransferRequest()->getAmount()->getAmount(), is(equalTo($amount)));
        assertThat($message->getTransferRequest()->getAmount()->getCurrency(), is(equalTo($currency)));
    }

    /**
     * @test
     */
    public function getDepositRequest_GivenMessageWithWithdrawRequest_ThrowsInvalidArgumentException()
    {
        $message = Message::withdraw(new WithdrawRequest(
            $transactionId = 'a89e5c76-2b14-495f-88e3-278003e90936',
            $accountId = 1,
            $amount = new Money(1000 * Money::MULTIPLIER, 'USD')
        ));

        $this->expectException(\InvalidArgumentException::class);
        $message->getDepositRequest();
    }

    /**
     * @test
     */
    public function getWithdrawRequest_GivenMessageWithTransferRequest_ThrowsInvalidArgumentException()
    {
        $message = Message::transfer(new TransferRequest(
            $transactionId = 'a89e5c76-2b14-495f-88e3-278003e90936',
            $fromAccountId = 1,
            $toAccountId = 2,
            $amount = new Money(1000 * Money::MULTIPLIER, 'USD')
        ));

        $this->expectException(\InvalidArgumentException::class);
        $message->getWihdrawRequest();
    }

    /**
     * @test
     */
    public function getTransferRequest_GivenMessageWithDepositRequest_ThrowsInvalidArgumentException()
    {
        $message = Message::deposit(new DepositRequest(
            $transactionId = 'a89e5c76-2b14-495f-88e3-278003e90936',
            $accountId = 1,
            $amount = new Money(1000 * Money::MULTIPLIER, 'USD')
        ));

        $this->expectException(\InvalidArgumentException::class);
        $message->getTransferRequest();
    }
}