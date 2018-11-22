<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Traits\ContainerAwareTrait;

class OrderCheckCommand extends ContainerAwareCommand
{
    use ContainerAwareTrait;

    protected function configure()
    {
        $this
            ->setName('order:check')
            ->setDescription('check new order')
            ->addOption('domain', 'd', InputOption::VALUE_OPTIONAL, 'domain')
            ->addArgument('number', InputArgument::REQUIRED, 'The number of the order')
            ->addArgument('type', InputArgument::REQUIRED, 'The type of the check 0-refuse,1-pass')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $number = $input->getArgument('number');
        $type = $input->getArgument('type');
        $this->check($input->getOption('domain'), $number, $type); 
    }

    private function check($domain, $number, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $modelOrder = $this->getRepo('AppBundle:Order')->findOneBy(['orderNo' => 'YYC2017102894109']);
        $order = $this->getRepo('AppBundle:Order')->findOneBy(['orderNo' => $number, 'status' => 1]);
        if(empty($order)) echo '订单编号错误';return;

        $parentReport = $modelOrder->getReport();
        $company = $order->getCompany()->getName();
        $config = $this->getRepo('AppBundle:Config')->find($company);
        $reCheck = $config->getNeedRecheck();

        $newReport = clone $parentReport;
        //将新报告的复审数据还原
        $newReport->setReport($parentReport->getReport());
        $newReport->setSecReport([]);
        $newReport->setRechecker(null);
        $newReport->setStartAt(null);
        $newReport->setEndAt(null);
        $em->persist($newReport);
        $em->flush();

        $order->setReport($newReport);
        if($reCheck) {
            $order->setStatus(3);
        } else {
            $order->setStatus(2);
        }
        $em->persist($order);
        $em->flush();
    }
}
