<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Traits\ContainerAwareTrait;

class FixAbnormalCommand extends ContainerAwareCommand
{
    use ContainerAwareTrait;

    protected function configure()
    {
        $this
            ->setName('fix:abnormal')
            ->setDescription('fix abnormal order associate to new order')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getDoctrine()->getManager();
        $batchSize = 100;
        $i = 0;
        $q = $em->createQuery('select o from AppBundle:Order o where o.status = 2 and o.operateLog is not null');

        $iterableResult = $q->iterate();

        foreach ($iterableResult as $row) {
            $order = $row[0];
            $operateLog = $order->getOperateLog();
            $oldOrderNo = mb_substr($operateLog,6,16);
            $oldOrder = $this->getRepo('AppBundle:Order')->findOneBy(['orderNo' => $oldOrderNo]);
            if ($oldOrder) {
                $oldOrder->setFork($order);
            }
            $output->writeln('orderId:'.$order->getId().',wait handle...');
            if (($i % $batchSize) === 0) {
                $em->flush(); // Executes all updates.
                $em->clear(); // Detaches all objects from Doctrine!
                $output->writeln('batch handle successfuly!');
            }
            ++$i;
        }

        $em->flush();
        $output->writeln('remain handle successfuly!');
    }
}