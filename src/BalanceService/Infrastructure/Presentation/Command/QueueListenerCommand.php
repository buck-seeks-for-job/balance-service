<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Infrastructure\Presentation\Command;

use Iqoption\BalanceService\Application\Deposit\DepositPerformer;
use Iqoption\BalanceService\Application\Transfer\TransferPerformer;
use Iqoption\BalanceService\Application\Withdraw\WithdrawPerformer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueueListenerCommand extends ContainerAwareCommand
{
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

    public function __construct(
        DepositPerformer $depositPerformer,
        WithdrawPerformer $withdrawPerformer,
        TransferPerformer $transferPerformer
    ) {
        parent::__construct();
        $this->depositPerformer = $depositPerformer;
        $this->withdrawPerformer = $withdrawPerformer;
        $this->transferPerformer = $transferPerformer;
    }

    protected function configure()
    {
        $this->setName('iqoption:queue:listen')->setDescription('Listens to RabbitMQ queue');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        while (true) {
        }
    }
}