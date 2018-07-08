<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Domain;

interface DatabaseTransactionManager
{
    /**
     * @param callable $operation
     * @return void
     */
    public function transactional(callable $operation): void;
}
