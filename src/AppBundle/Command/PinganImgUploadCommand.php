<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * 手动上传平安云接口的command
 */
class PinganImgUploadCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->addArgument('orderId', InputArgument::REQUIRED, 'orderId needed')
            ->setName('upload:start')
            ->setDescription('manual upload img to pinganyun')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $orderId = $input->getArgument('orderId');

        $this->getContainer()->get('ReportLogic')->handlePinganPicture($orderId);
    }
}