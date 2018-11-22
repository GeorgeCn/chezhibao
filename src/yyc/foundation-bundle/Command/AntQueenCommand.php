<?php

namespace YYC\FoundationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AntQueenCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('YYCFoundation:antqueen')
            ->setDescription('蚂蚁女王辅助命令')
            ->addArgument('cmd', InputArgument::REQUIRED, '子命令, configurl, notify')
            ->addOption('domain', 'd', InputOption::VALUE_OPTIONAL, 'domain')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cmd = $input->getArgument('cmd');
        if ($cmd == "notify"){
            $this->notify($input->getOption('domain'));
        }
    }

    private function notify($domain)
    {
        $url = "http://$domain{$this->getContainer()->get('router')->generate('yyc_antqueen_notify', [], UrlGeneratorInterface::ABSOLUTE_PATH)}";
        var_dump($url);
        $data = $this->getContainer()->get("yyc_foundation.third.antqueen")->getMockData();
        $ret = $this->getContainer()->get('util.curl_helper')->post($url, $data);
        var_dump($ret);
    }
}
