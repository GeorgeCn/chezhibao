<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Traits\ContainerAwareTrait;

class FixGradeCommand extends ContainerAwareCommand
{
    use ContainerAwareTrait;

    protected function configure()
    {
        $this
            ->setName('fix:grade')
            ->setDescription('fix agencyRel grade')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getDoctrine()->getManager();
        $batchSize = 100;
        $i = 0;
        $q = $em->createQuery('select a from AppBundle:AgencyRel a where a.grade = 0');
        $iterableResult = $q->iterate();
        foreach ($iterableResult as $row) {
            $agencyRel = $row[0];
            $user = $agencyRel->getUser();
            $roles = $user->getRoles();
            if (in_array('ROLE_ADMIN_HPL', $roles)) {
                $agencyRel->setGrade(1);
            } elseif (in_array('ROLE_LOADOFFICER_MANAGER', $roles)) {
                $agencyRel->setGrade(2);
            } elseif (in_array('ROLE_LOADOFFICER', $roles)) {
                $agencyRel->setGrade(3);
            } else {
                continue;
            }

            $output->writeln('userId:'.$user->getId().',wait handle...');
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