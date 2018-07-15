<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Domain;

use Iqoption\BalanceService\Common\Money;

interface BalanceCalculator
{
    public function calculate(int $accountId): Money;
}
