<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Traits\ContainerAwareTrait;

/**
 * 根据输入参数获取sign
 */
class GetSignCommand extends ContainerAwareCommand
{
    use ContainerAwareTrait;

    protected function configure()
    {
        $this
            ->setName('get-sign')
            ->setDescription('get sign for api')
            ->addArgument('key', InputArgument::REQUIRED, 'company key')
            ->addArgument('type', InputArgument::REQUIRED, 'api interface type include 1,2,3')
            ->addArgument('number', InputArgument::REQUIRED, 'business number')
            ->addArgument('orderNo', InputArgument::REQUIRED, 'order no')
            ->addArgument('timestamp', InputArgument::REQUIRED, 'timestamp')
            ->addArgument('secret', InputArgument::REQUIRED, 'secret')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $systemApiSign = $this->get('util.systemapisign');
        $retSign = $systemApiSign->enSign(
            [
                'key' => $input->getArgument('key'),
                'type' => $input->getArgument('type'),
                'number' => $input->getArgument('number'),
                'orderNo' => $input->getArgument('orderNo'),
                'timestamp' => $input->getArgument('timestamp'),
                'serect' => $input->getArgument('secret'),
            ]
        );

        $output->writeln('the sign is:'.$retSign);
    }
}