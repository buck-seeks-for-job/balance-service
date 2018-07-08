<?php
declare(strict_types=1);

namespace Iqoption\Test\Unit\BalanceService;

use Iqoption\BalanceService\Domain\Account\Account;
use Iqoption\BalanceService\Domain\Account\NominalAccount;
use Iqoption\BalanceService\Domain\Account\UserAccount;

trait AccountAwareTestCase
{
    private function givenNominalAccount(string $ownerId, string $currency): int
    {
        $account = new NominalAccount($ownerId, $currency);

        self::$entityManager->persist($account);
        self::$entityManager->flush();

        return (int)$this->getFieldFromAccount($account, 'id');
    }

    private function givenUserAccount(string $name, string $currency)
    {
        $account = new UserAccount($name, $currency);

        self::$entityManager->persist($account);
        self::$entityManager->flush();

        return (int)$this->getFieldFromAccount($account, 'id');
    }

    private function getFieldFromAccount(Account $account, string $name)
    {
        $reflection = new \ReflectionProperty(get_class($account), $name);
        $reflection->setAccessible(true);

        return $reflection->getValue($account);
    }
}