<?php
declare(strict_types=1);

namespace Iqoption\BalanceService\Infrastructure\Presentation\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueueListenerCommand extends ContainerAwareCommand
{
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