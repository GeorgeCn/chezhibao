<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompanyNotifyCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->addArgument('orderNo', InputArgument::REQUIRED, '评估单号')
            ->setName('jiance:notify_company')
            ->setDescription('通知订单公司')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $orderNo = $input->getArgument('orderNo');
        $this->getContainer()->get("util.rabbitmq")->sendCompanyNotify($orderNo);
    }
}