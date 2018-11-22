<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Traits\ContainerAwareTrait;

/**
 * 更新所有订单中的省份和城市
 */
class FixCityCommand extends ContainerAwareCommand
{
    use ContainerAwareTrait;

    protected function configure()
    {
        $this
            ->setName('fix:city')
            ->setDescription('fix the city for all months')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {   
        $em = $this->getDoctrine()->getManager();
        $q = $em->createQuery('select o from AppBundle:Order o where o.status = 2');
        $iterableResult = $q->iterate();
        foreach ($iterableResult as $row) {
            $order = $row[0];
            $output->writeln('orderId:'.$order->getId().',wait update address...');
            $this->get('OrderLogic')->updateOrderAddress($order);
            $em->clear();
            $output->writeln('update address finish');
        }
    }
}