<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Traits\ContainerAwareTrait;

class FixOrderCommand extends ContainerAwareCommand
{
    use ContainerAwareTrait;

    protected function configure()
    {
        $this
            ->setName('fix:order')
            ->setDescription('fix order agencyId, agencyName, AgencyCode')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getDoctrine()->getManager();
        $batchSize = 100;
        $i = 0;
        $q = $em->createQuery('select o from AppBundle:Order o where o.agencyId is null and o.agencyName is null and o.agencyCode is null');

        $iterableResult = $q->iterate();

        foreach ($iterableResult as $row) {
            $order = $row[0];

            $user = $order->getLoadOfficer();
            if (!$user) {
                continue;
            }

            $agencyRels = $user->getAgencyRels();
            if (!$agencyRels[0]) {
                continue;
            }

            $agency = $agencyRels[0]->getAgency();
            if (!$agency) {
                continue;
            }

            $order->setAgencyId($agency->getId())
                ->setAgencyName($agency->getName())
                ->setAgencyCode($agency->getCode())
            ;

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