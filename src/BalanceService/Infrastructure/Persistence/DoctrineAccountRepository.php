<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Infrastructure\Persistence;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Iqoption\BalanceService\Application\Exception\AccountNotFoundException;
use Iqoption\BalanceService\Application\Exception\NoNominalAccountException;
use Iqoption\BalanceService\Domain\Account\Account;
use Iqoption\BalanceService\Domain\Account\AccountRepository;
use Iqoption\BalanceService\Domain\Account\NominalAccount;
use Iqoption\BalanceService\Domain\Account\UserAccount;

class DoctrineAccountRepository extends EntityRepository implements AccountRepository
{
    public function add(Account $account): void
    {
        $this->getEntityManager()->persist($account);
    }

    public function findUserAccountById(int $id): UserAccount
    {
        $account = $this->find($id);

        if ($account === null || !($account instanceof UserAccount)) {
            throw new AccountNotFoundException;
        }

        return $account;
    }

    public function findNominalAccountByOwnerAndCurrency(string $ownerId, string $currency): NominalAccount
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('a')
            ->from(NominalAccount::class, 'a')
            ->where('a.ownerId = :id')
            ->andWhere('a.currency = :currency')
            ->getQuery();
        $query->setParameter('id', $ownerId);
        $query->setParameter('currency', $currency);

        try {
            return $query->getSingleResult();
        } catch (NoResultException $e) {
            throw new NoNominalAccountException;
        }
    }

    public function findAndLockById(int $id): UserAccount
    {
        $account = $this->find($id, LockMode::PESSIMISTIC_WRITE);

        if ($account === null || !($account instanceof UserAccount)) {
            throw new AccountNotFoundException;
        }

        return $account;
    }
}