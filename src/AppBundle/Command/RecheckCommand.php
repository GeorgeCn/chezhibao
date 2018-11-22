<?php

namespace AppBundle\Command;

use AppBundle\Entity\Order;
use AppBundle\Traits\ContainerAwareTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RecheckCommand extends ContainerAwareCommand
{
    use ContainerAwareTrait;

    protected function configure()
    {
        $this
            ->setName('recheck:handle')
            ->setDescription('handle order recheck status')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $n = 5;
        while ($n--) {
            $this->_do($input, $output);
            sleep(8);
        }
    }

    protected function _do(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('start time:' . date('Y-m-d H:i:s'));
        $em        = $this->getDoctrine()->getManager();
        $batchSize = 100;
        $i         = 0;
        $dateTime  = new \DateTime();
        $dateTime->modify('-27 minutes');
        $q = $em->createQuery('select o from AppBundle:Order o join o.report r join r.examer u where o.status = :status and u.noob != 1 and r.locked != 1 and o.submitedAt <= :dateTime and r.hplExaming != 1')
            ->setParameters(['status' => Order::STATUS_RECHECK, 'dateTime' => $dateTime])
        ;

        $iterableResult = $q->iterate();
        foreach ($iterableResult as $row) {
            $order  = $row[0];
            $report = $order->getReport();
            if ($report->getReport()['field_result']['value'] == '评估通过') {
                $this->get('ReportLogic')->passReport($report);
            } else {
                $this->get('ReportLogic')->refuseReport($report);
            }

            $output->writeln('orderId:' . $order->getId() . ',wait handle...');
            if (($i % $batchSize) === 0) {
                $em->flush(); // Executes all updates.
                $em->clear(); // Detaches all objects from Doctrine!
                $output->writeln('batch handle successfuly!');
            }
            ++$i;
        }

        $em->flush();
        $em->clear();
        $output->writeln('remain handle successfuly!');
        $output->writeln('end time:' . date('Y-m-d H:i:s'));
    }
}
