<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Traits\ContainerAwareTrait;

class OrderBackCommand extends ContainerAwareCommand
{
    use ContainerAwareTrait;

    protected function configure()
    {
        $this
            ->setName('order:back')
            ->setDescription('Submit new order')
            ->addOption('domain', 'd', InputOption::VALUE_OPTIONAL, 'domain')
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'username')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'password')
            ->addArgument('number', InputArgument::REQUIRED, 'The number of the order')
            ->addArgument('type', InputArgument::REQUIRED, 'The type of the backreason')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $number = $input->getArgument('number');
        $type = $input->getArgument('type');
        $username = $input->getOption('username') ? $input->getOption('username') : 'admin';
        $password = $input->getOption('password') ? $input->getOption('password') : 'admin';
        $this->back($input->getOption('domain'), $username, $password, $number, $type); 
    }

    private function back($domain, $username, $password, $number, $type)
    {
        $form = '{"reason_10":{"value":"\u4e0d\u6e05\u6670"},"reason_20":{"value":"\u4e0d\u6e05\u6670"},"reason_30":{"value":"\u6b63\u5e38"},"reason_40":{"value":"\u6b63\u5e38"},"reason_50":{"value":"\u6b63\u5e38"},"reason_60":{"value":"\u6b63\u5e38"},"reason_70":{"value":"\u6b63\u5e38"},"reason_80":{"value":"\u6b63\u5e38"},"reason_90":{"value":"\u6b63\u5e38"},"reason_100":{"value":"\u6b63\u5e38"},"reason_110":{"value":"\u6b63\u5e38"},"reason_120":{"value":"\u6b63\u5e38"},"reason_130":{"value":"\u6b63\u5e38"},"reason_140":{"value":"\u6b63\u5e38"},"reason_150":{"value":"\u6b63\u5e38"},"reason_160":{"value":"\u6b63\u5e38"},"reason_170":{"value":"\u6b63\u5e38"},"reason_180":{"value":"\u6b63\u5e38"},"reason_190":{"value":"\u6b63\u5e38"},"reason_200":{"value":"\u6b63\u5e38"},"reason_210":{"value":"\u6b63\u5e38"},"reason_220":{"value":"\u6b63\u5e38"},"reason_230":{"value":"\u6b63\u5e38"},"reason_240":{"value":"\u6b63\u5e38"},"reason_250":{"value":"\u6b63\u5e38"},"reason_260":{"value":"\u6b63\u5e38"},"reason_270":{"value":"\u6b63\u5e38"},"reason_280":{"value":"\u6b63\u5e38"},"reason_290":{"value":"\u6b63\u5e38"},"reason_300":{"value":"\u6b63\u5e38"},"reason_310":{"value":"\u6b63\u5e38"},"reason_320":{"value":"\u6b63\u5e38"},"reason_330":{"value":"\u6b63\u5e38"},"reason_340":{"value":"\u6b63\u5e38"},"reason_350":{"value":"\u6b63\u5e38"},"reason_360":{"value":"\u6b63\u5e38"},"reason_370":{"value":"\u6b63\u5e38"},"reason_380":{"value":"\u6b63\u5e38"},"reason_v1":{"value":"\u6b63\u5e38"},"mainReason":"ggg"}';
        $arr_form = json_decode($form, true);
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager();
        foreach ($mm->getMetadata4BackReason() as $metadata) {
            if (!empty($arr_form[$metadata->key])) {
                $backData[$metadata->key] = $metadata->makeValue($arr_form[$metadata->key]);
            }
        }

        foreach ($mm->getMetadata4BackReasonVideo() as $metadatas) {
            if (!empty($arr_form[$metadatas->key])) {
                $backData[$metadatas->key] = $metadata->makeValue($arr_form[$metadatas->key]);
            }
        }
        $em = $this->getDoctrine()->getManager();
        $orderBack = $this->get('ReportLogic')->createOrderBack($report, $backData, $arr_form['mainReason']);

        if ($orderBack) {
            $order = $em->getRepository('AppBundle:Report')->findOrder($report->getId());
            $order->setLocked(false);
            $order->setLockOwner(null);
            $em->flush();
        }
    }
}
