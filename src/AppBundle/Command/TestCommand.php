<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Traits\ContainerAwareTrait;

class TestCommand extends ContainerAwareCommand
{
    use ContainerAwareTrait;

    protected function configure()
    {
        $this
            ->setName('test')
            ->setDescription('notify false status to hpl interface')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->get('app.third.sms')->send('yjc_examer_back', '13818432902', ['test']);
        $this->get('app.third.sms')->send('yjc_examer_pass', '13818432902', ['test']);
        $this->get('app.third.sms')->send('yjc_examer_refuse', '13818432902', ['test']);
        $this->get('app.third.sms')->send('yjc_sms_code', '13818432902', ['test']);
    }
}