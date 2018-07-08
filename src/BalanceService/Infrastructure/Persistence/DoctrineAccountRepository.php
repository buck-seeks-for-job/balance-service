<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Infrastructure\Persistence;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Iqoption\BalanceService\Application\Exception\AccountNotFoundException;
use Iqoption\BalanceService\Application\Exception\NoNominalAccountException;
use Iqoption\BalanceService\Domain\Account\Account;
use Iqoption\BalanceService\Domain\Account\AccountRepository;
use Iqoption\BalanceService\Domain\Account\NominalAccount;

class DoctrineAccountRepository extends EntityRepository implements AccountRepository
{
    public function add(Account $account): void
    {
        $this->getEntityManager()->persist($account);
    }

    public function findById(int $id): Account
    {
        $account = $this->find($id);

        if ($account === null) {
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
}