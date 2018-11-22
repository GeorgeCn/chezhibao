<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Traits\ContainerAwareTrait;

/**
 * 导出report中的价格
 */
class ExportPriceCommand extends ContainerAwareCommand
{
    use ContainerAwareTrait;

    protected function configure()
    {
        $this
            ->setName('export:price')
            ->addArgument('start_date', InputArgument::OPTIONAL, 'examedAt start date Y-m-d format')
            ->addArgument('end_date', InputArgument::OPTIONAL, 'examedAt end date Y-m-d format')
            ->setDescription('export report price for evaluate')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $str = '';
        $head = ['订单id', '品牌', '车系', '车型', '上牌日期', '品牌id', '车系id', '车型id', '年款', '公里数', '城市', '收购价', '销售价', '审核时间', '公司'];
        $str = implode(',', $head)."\r\n";
        $fileName = date('Y-m-d-H-i-s').'.csv';
        $fp = fopen($fileName, "a");
        fwrite($fp, $str);

        $em = $this->getDoctrine()->getManager();
        $q = $this->getRepo('AppBundle:Order')->findOrderReportPrice($input->getArgument('start_date'), $input->getArgument('end_date'));
        $data = [];
        $batchSize = 1000;
        $i = 0;
        $iterableResult = $q->iterate();
        foreach ($iterableResult as $row) {
            $order = $row[0];
            $report = $order->getReport();
            $purchasePrice = $report->getPurchasePrice();
            $sellPrice = $report->getSellPrice();

            $data = [
                $order->getId(),
                $report->getBrand(),
                $report->getSeries(),
                $report->getModel(),
                $report->getRegisterDate(),
                $report->getBrandId(),
                $report->getSeriesId(),
                $report->getModelId(),
                $report->getYear(),
                $report->getKilometer(),
                $order->getCarCity(),
                $purchasePrice,
                $sellPrice,
                $examedAt = $report->getExamedAt()->format('Y-m-d H:i:s'),
                $company = $order->getCompany()->getCompany(),
            ];
            $str = implode(',', $data)."\r\n";
            fwrite($fp, $str);
            $output->writeln('reportId:'.$report->getId().',csv imported');
            $output->writeln('reportId:'.$report->getId().',wait handle...');

            if (($i % $batchSize) === 0) {
                $em->clear(); // Detaches all objects from Doctrine!
                $output->writeln('batch handle successfuly!');
            }
            ++$i;
        }
        $em->clear();
        fclose($fp);
        $output->writeln('remain handle successfuly!');
    }
}