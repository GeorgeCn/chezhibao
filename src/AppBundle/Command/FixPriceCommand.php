<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Traits\ContainerAwareTrait;

class FixPriceCommand extends ContainerAwareCommand
{
    use ContainerAwareTrait;

    protected function configure()
    {
        $this
            ->setName('fix:price')
            ->setDescription('fix sellPrice, purchasePrice...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getDoctrine()->getManager();
        $batchSize = 100;
        $i = 0;
        $q = $em->createQuery('select r from AppBundle:Report r');
        $iterableResult = $q->iterate();
        foreach ($iterableResult as $row) {
            $report = $row[0];
            $sellPrice = isset($report->getReport()['field_4012']) ? $report->getReport()['field_4012']['value'] : '';
            $purchasePrice = isset($report->getReport()['field_4010']) ? $report->getReport()['field_4010']['value'] : '';
            $guidePrice = isset($report->getReport()['field_4020']) ? $report->getReport()['field_4020']['value'] : '';
            $futurePrice = isset($report->getReport()['field_4014']) ? $report->getReport()['field_4014']['value'] : '';
            $kilometer = isset($report->getReport()['field_3010']) ? $report->getReport()['field_3010']['value'] : '';
            $registerDate = isset($report->getReport()['field_1060']) ? $report->getReport()['field_1060']['value'] : '';

            if ($sellPrice) {
                $report->setSellPrice($sellPrice);
            }

            if ($purchasePrice) {
                $report->setPurchasePrice($purchasePrice);
            }

            if ($guidePrice) {
                $report->setGuidePrice($guidePrice);
            }

            if ($futurePrice) {
                $report->setFuturePrice($futurePrice);
            }

            if ($kilometer) {
                $report->setKilometer($kilometer);
            }

            if ($registerDate) {
                $report->setRegisterDate($registerDate);
            }

            $output->writeln('reportId:'.$report->getId().',wait copy...');
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