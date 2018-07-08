<?php
declare(strict_types=1);

namespace Iqoption\Test\Unit\BalanceService\Application\Account;

use Iqoption\BalanceService\Application\Account\UserAccountCreator;
use Iqoption\BalanceService\Domain\Account\Account;
use Iqoption\BalanceService\Domain\Account\UserAccount;
use Iqoption\BalanceService\Infrastructure\Persistence\DoctrineTransactionManager;
use Iqoption\Test\TestUtility\DoctrineSqliteTestCase;
use Iqoption\Test\Unit\BalanceService\AccountAwareTestCase;

class UserAccountCreatorTest extends DoctrineSqliteTestCase
{
    use AccountAwareTestCase;

    /**
     * @var UserAccountCreator
     */
    private $userAccountCreator;

    protected function setUp()
    {
        parent::setUp();

        $this->userAccountCreator = new UserAccountCreator(
            self::$entityManager->getRepository(Account::class),
            new DoctrineTransactionManager(self::$entityManager)
        );
    }

    /**
     * @test
     */
    public function create_GivenNameAndCurrency_CreatesUserAccount()
    {
        $id = $this->userAccountCreator->create($name = 'Robin Bobin', $currency = 'RUB');

        $this->assertThatUserAccountCreated($id, [
            'name' => $name,
            'currency' => $currency
        ]);
    }

    protected static function getAnnotationMetadataConfigurationPaths(): array
    {
        return [
            self::getClassDirectory(Account::class)
        ];
    }

    private function assertThatUserAccountCreated(int $id, array $expectedFieldMap)
    {
        $account = self::$entityManager->find(UserAccount::class, $id);

        foreach ($expectedFieldMap as $name => $expectedValue) {
             assertThat($this->getFieldFromAccount($account, $name), is(equalTo($expectedValue)));
        }
    }
}